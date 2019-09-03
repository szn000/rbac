<?php
namespace sunzhaonan\rbac;


use sunzhaonan\nestedsets\NestedSets;
use sunzhaonan\rbac\model\ManagerRole;
use sunzhaonan\rbac\model\Permission;
use sunzhaonan\rbac\model\PermissionCategory;
use sunzhaonan\rbac\model\PermissionResources;
use sunzhaonan\rbac\model\Resources;
use sunzhaonan\rbac\model\Role;
use sunzhaonan\rbac\model\RolePermission;
use sunzhaonan\rbac\model\UserRole;
use think\Db;
use think\db\Query;
use think\db\Where;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Session;

class Rbac
{

    const DEFAULT_CONDITION = ['status' => 1];

    public function __construct()
    {
        $rbacConfig = config('rbac');
        if (!empty($rbacConfig)) {
            isset($rbacConfig['db']) && $this->db = $rbacConfig['db'];
        }

    }

    /**
     * 初始化数据库表 
     * @param string $db
     */
    public function initTable($db = '')
    {
        $createTable = new CreateTable();
        $createTable->create($db);
    }

    /**
     * 配置参数
     * @param string $db
     */
    public function setDb($db = '')
    {
       $this->db = $db;
    }


    public function createResources(array $data = [])
    {
        $model = new Resources($this->db);
        $model->data($data);
        try{
            $res = $model->saveResources();
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function editResources(array $data = [], $id = null)
    {
        if (!empty($id)) {
            $data['id'] = $id;
        }
        try{
            return $this->createResources($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delResources($id = 0)
    {
        $model = new Resources($this->db);
        try {
            return $model->delResources($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delResourcesBatch($condition)
    {
        $model = new Resources($this->db);
        if ($model->where($condition)->delete() === false) {
            throw new Exception('批量删除数据出错');
        }
        return true;
    }

    public function createPermission(array $data = [], $resourcesIds= '')
    {
        $model = new Permission($this->db);
        $model->data($data);
        try{
            $res = $model->savePermission($resourcesIds);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    public function getPermission($condition, $withResourcesId = true)
    {
        $model = new Permission($this->db);
        return $model->getPermission($condition, $withPermissionId);
    }

    public function delPermission($id)
    {
        $model = new Permission($this->db);
        try {
            $res = $model->delPermission($id);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createRole(array $data = [], $permissionIds = '')
    {
        $model = new Role($this->db);
        $model->data($data);
        try{
            $res = $model->saveRole($permissionIds);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    public function getRole($condition, $withPermissionId = true)
    {
        $model = new Role($this->db);
        return $model->getRole($condition, $withPermissionId);
    }

    public function delRole($id)
    {
        $model = new Role($this->db);
        try {
            $res = $model->delRole($id);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assignManagerRole($managerId, array $role = [])
    {
        if (empty($managerId) || empty($role)) {
            throw new Exception('参数错误');
        }
        $model = new ManagerRole($this->db);
        $model->startTrans();
        if ($model->where('manager_id', $managerId)->delete() === false) {
            $model->rollback();
            throw new Exception('删除用户原有角色出错');
        }
        $managerRole = [];
        foreach ($role as $v)
        {
            $managerRole [] = ['manager_id' => $managerId, 'role_id' => $v];
        }
        if ($model->saveAll($managerRole) === false) {
            $model->rollback();
            throw new Exception('给用户分配角色出错');
        }
        $model->commit();
        return ;
    }

    public function delManagerRole($id)
    {
        if (empty($id)) {
            throw new Exception('参数错误');
        }
        $model = new ManagerRole($this->db);
        if ($model->where('manager_id', $id)->delete() === false) {
            throw new Exception('删除用户角色出错');
        }
        return true;
    }

    public function verifyRequest($controller,$action,$method)
    {
        $result = true;
        $resourcesId = $this->verifyResources($controller,$action,$method);
        if ($resourcesId) {
            $permissionId = $this->verifyPermission($resourcesId);
            if ($permissionId) {
                $roleId = $this->verifyRole($permissionId);
                if ($roleId) {
                    $result = $this->verifyManager($roleId);
                } 
            }
        }
        return $result;
    }
    
    private function verifyManager($roleId)
    {
        $result = false;
        if (Cache::has('rbac_manger')) {
            $manager = Cache::get('rbac_manager');
            $manager = unserialize($manager);
            foreach ($manager as $key => $value) {
                if ($value == $managerId) {
                    $result = $key;
                    break;
                }
            }
        }
        return $result;
    }
    
    private function verifyRole($permissionId) 
    {
        $result = false;
        if (Cache::has('rbac_role')) {
            $role = Cache::get('rbac_role');
            $role = unserialize($role);
            foreach ($role as $key => $value) {
                if (in_array($permissionId,$value)) {
                    $result = $key;
                    break;
                }
            }
        }
        return $result;
    }

    private function verifyPermission($resourcesId) 
    {
        $result = false;
        if (Cache::has('rbac_permission')) {
            $permission = Cache::get('rbac_permission');
            $permission = unserialize($permission);
            foreach ($permission as $key => $value) {
                if (in_array($resourcesId,$value)) {
                    $result = $key;
                    break;
                } 
            }
        }
        return $result;
    }


    private function verifyResources($controller,$action,$method)
    {
        if (Cache::has('rbac_resources')) {
            $resources = Cache::get('rbac_resources');
            $resources = unserialize($resources);
            if (!isset($resources[$controller][$action])) {
                $result = false;
            } else {
                $nowResources = $resources[$controller][$action];
                if ($method == $nowResources['method']) {
                    $resulr = $nowResources['id'];
                } else {
                    $result = false;
                }
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存权限信息 到缓存
     * 传递参数为true 刷新缓存
     * @param bool $stillCache
     * @param [] $type 刷新缓存类型 [] 全部  manager_role role role_permission permission permission_resources resources
     */
    public function cacheInit($type = [], $stillCache = false)
    {
        if (empty($type)) {
            if (!Cache::has('rbac_manager') || $stillCache) {
                $this->cacheManager();
            }
            if (!Cache::has('rbac_role') || $stillCache) {
                $this->cacheRole();
            }
            if (!Cache::has('rbac_permission') || $stillCache) {
                $this->cachePermission();
            }
            if (!Cache::has('rbac_resources') || $stillCache) {
                $this->cacheResources();
            }
        } else {
            foreach ($type as $value) {
                switch ($value) {
                    case "manger":
                        $this->cacheManager();
                        break;
                    case "role":
                        $this->cacheRole();
                        break;
                    case "permission":
                        $this->cachePermission();
                        break;
                    case "resources":
                        $this->cacheResources();
                        break;
                }
            }
        }
    }
    
    private function cacheManager()
    {
        $managerRole = new ManagerRole();
        $managerRoleResult = $managerRole->getManagerRole(self::DEFAULT_CONDITION);
        $managerRoleArr = [];
        if (!empty($managerRoleResult)) {
            foreach($managerRoleResult as $managerRoleRow) {
                $managerRoleArr[$managerRoleRow['manager_id']] = $managerRoleRow['role_id'];
            }
        }
        $managerRoleArr = serialize($managerRoleArr);
        Cache::set('rbac_manager',$managerRoleArr);
    }


    private function cacheRole()
    {
        $rolePermission = new RolePermission();
        $rolePermissionResult = $rolePermission->getRolePermission(self::DEFAULT_CONDITION);
        $rolePermissionArr = [];
        if (!empty($rolePermissionResult)) {
            foreach($rolePermissionResult as $rolePermissionRow) {
                if (empty($rolePermissionArr[$rolePermissionRow['role_id']])) {
                    $rolePermissionArr[$rolePermissionRow['role_id']] = [];
                }
                $rolePermissionArr[$rolePermissionRow['role_id']][] = $rolePermissionRow['permission_id'];
            }
            
        }
        $rolePermissionArr = serialize($rolePermissionArr);
        Cache::set('rbac_role',$rolePermissionArr);
    }


    private function cachePermission()
    {
        $permissionResources = new PermissionResources();
        $permissionResourcesResult = $permissionResources->getPermissionResources(self::DEFAULT_CONDITION);
        $permissionResourcesArr = [];
        if (!empty($permissionResourcesResult)) {
            foreach($permissionResourcesResult as $permissionResourcesRow) {
                if (empty($permissionResourcesArr[$permissionResourcesRow['permission_id']])) {
                    $permissionResourcesArr[$permissionResourcesRow['permission_id']] = [];
                }
                $permissionResourcesArr[$permissionResourcesRow['permission_id']][] = $permissionResourcesRow['resources_id'];
            }
            
        }
        $permissionResourcesArr = serialize($permissionResourcesArr);
        Cache::set('rbac_permission',$permissionResourcesArr);
    }

    private function cacheResources()
    {
        $resources = new Resources();
        $resourcesResult = $resources->getResources(self::DEFAULT_CONDITION);
        $resourcesArr = [];
        if (!empty($resourcesResult)) {
            foreach ($resourcesResult as $resourcesRow) {
                if (empty($resourcesArr[$resourcesRow['module']])) {
                    $resourcesArr[$resourcesRow['module']] = [];
                }
                $resourcesArr[$resourcesRow['module']][$resourcesRow['operate']] = $resourcesRow;
            }
        }
        $resourcesArr = serialize($resourcesArr);
        Cache::set('rbac_resources',$resourcesArr);
    }
}
