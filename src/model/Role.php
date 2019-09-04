<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class Role extends Base
{
        /**
     * 编辑角色
     * @param string $permissionIds
     * @param array $data
     * @return $this
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function saveRole($permissionIds = '', $data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $validate = new \sunzhaonan\rbac\validate\Role();
        if (!$validate->check($this)) {
            throw new Exception($validate->getError());
        }
        $data = $this->getData();
        if (isset($data['id']) && !empty($data['id'])) {
            $this->isUpdate(true);
        }
        $this->startTrans();
        $this->save();
        //如果有权限的情况下
        if (empty($permissionIds)) {
            return $this;
        }
        $permissionIdsArr = array_filter(explode(',', $permissionIds));
        if (empty($permissionIdsArr)) {
            return $this;
        }
        //删除原有权限
        $rolePermission = new RolePermission($this->connection);
        if ($rolePermission->where('role_id', $this->id)->delete() === false) {
            $this->rollback();
            throw new Exception('删除原有权限时出错');
        }
        $writeData = [];
        foreach ($permissionIdsArr as $v)
        {
            $writeData[] = [
                'role_id' => $this->id,
                'permission_id' => $v
            ];
        }
        if ($rolePermission->saveAll($writeData) === false) {
            $this->rollback();
            throw new Exception('写入角色权限时出错');
        }
        $this->commit();
        return $this;
    }

    /**
     * 删除角色
     * @param $condition
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function delRole($condition)
    {
        $where = [];
        $relationWhere = [];
        if (is_array($condition)) {
            $where[] = ['id', 'IN', $condition];
            $relationWhere[] = ['role_id', 'IN', $condition];
        } else {
            $id = (int)$condition;
            if (is_numeric($id) && $id > 0) {
                $where[] = ['id', '=', $id];
                $relationWhere[] = ['role_id', '=', $condition];
            } else {
                throw new Exception('删除条件错误');
            }
        }
        $this->startTrans();
        if ($this->where($where)->delete() === false) {
            $this->rollback();
            throw new Exception('删除角色出错');
        }
        $rolePermission = new RolePermission($this->connection);
        if ($rolePermission->where($relationWhere)->delete() === false) {
            $this->rollback();
            throw new Exception('删除角色关联权限出错');
        }
        $this->commit();
        return true;
    }

    /**
     * 获取角色列表
     * @param $condition
     * @param bool $withPermissionId
     * @return array|\PDOStatement|string|\think\Collection|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRole($condition, $withPermissionId = false)
    {
        $model = Db::name('role')->setConnection($this->getConnection());
        $where = [];
        if (is_array($condition)) {
            $where = $condition;
        } else {
            $condition = (int)$condition;
            if (is_numeric($condition) && $condition > 0) {
                $role = $model->where('id', $condition)->find();
                if (!empty($role) && $withPermissionId) {
                    $rolePermission = Db::name('role_permission')->setConnection($this->getConnection())
                        ->leftJoin('permission','role_permission.permission_id=permission.id')
                        ->where('role_id', $condition)->select();
                    $roleIdIndexer = [];
                    $roleNameIndexer = [];
                    if (!empty($rolePermission)) {
                        foreach ($rolePermission as $v)
                        {
                            $roleIdIndexer[] = $v['permission_id'];
                            $roleNameIndexer[] = $v['name'];
                        }
                    }
                    $role['permissionIds'] = $roleIdIndexer;
                    $role['permissionNames'] = $roleNameIndexer;
                }
                return $role;
            }
        }
        $role = Db::name('role')->setConnection($this->getConnection())
            ->where($where)->select();
        if (!empty($role) && $withPermissionId) {
            $permission = Db::name('role_permission')->setConnection($this->getConnection())
                ->leftJoin('permission','role_permission.permission_id=permission.id')
                ->where('role_id', 'IN', array_column($role, 'id'))
                ->select();
            $roleIdIndexer = [];
            $roleNameIndexer = [];
            if (!empty($permission)) {
                foreach ($permission as $v)
                {
                    $roleIdIndexer[$v['role_id']][] = $v['permission_id'];
                    $roleNameIndexer[$v['role_id']][] = $v['name'];
                }
            }
            foreach ($role as &$v)
            {
                $v['permissionIds'] = isset($roleIdIndexer[$v['id']])? $roleIdIndexer[$v['id']] : [];
                $v['permissionNames'] = isset($roleNameIndexer[$v['id']])? $roleNameIndexer[$v['id']] : [];
                unset($v);
            }
        }
        return $role;
    }

}