<?php
namespace sunzhaonan\rbac;


use sunzhaonan\nestedsets\NestedSets;
use sunzhaonan\rbac\model\ManagerRole;
use sunzhaonan\rbac\model\Permission;
use sunzhaonan\rbac\model\PermissionCategory;
use sunzhaonan\rbac\model\Resources;
use sunzhaonan\rbac\model\Role;
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


    public function cacheManagerResources()
    {

    }

    public function can()
    {

    }
}
