<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class Permission extends Base
{
    /**
     * 编辑权限分组
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function savePermission($resourcesIds, $data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $validate = new \gmars\rbac\validate\PermissionCategory();
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
        if (empty($resourcesIds)) {
            $this->rollback();
            throw new Exception('空的资源出错');
            return $this;
        }
        $resourcesIdsArr = array_filter(explode(',', $permissionIds));
        if (empty($resourcesIdsArr)) {
            $this->rollback();
            throw new Exception('空的资源出错');
            return $this;
        }
        //删除原有权限
        $permissionResources = new PermissionResources($this->connection);
        if ($permissionResources->where('permission_id', $this->id)->delete() === false) {
            $this->rollback();
            throw new Exception('删除原有资源时出错');
        }
        $writeData = [];
        foreach ($permissionIdsArr as $v)
        {
            $writeData[] = [
                'resources_id' => $this->id,
                'permission_id' => $v
            ];
        }
        if ($permissionResources->saveAll($writeData) === false) {
            $this->rollback();
            throw new Exception('写入权限资源时出错');
        }
        $this->commit();
        return $this;
    }

    /**
     * 删除权限分组
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delPermission($condition)
    {

        $where = [];
        $relationWhere = [];
        if (is_array($condition)) {
            $where[] = ['id', 'IN', $condition];
            $relationWhere[] = ['permission_id', 'IN', $condition];
        } else {
            $id = (int)$condition;
            if (is_numeric($id) && $id > 0) {
                $where[] = ['id', '=', $id];
                $relationWhere[] = ['permission_id', '=', $condition];
            } else {
                throw new Exception('删除条件错误');
            }
        }
        $this->startTrans();
        if ($this->where($where)->delete() === false) {
            $this->rollback();
            throw new Exception('删除权限出错');
        }
        $permissionResources = new PermissionResources($this->connection);
        if ($permissionResources->where($relationWhere)->delete() === false) {
            $this->rollback();
            throw new Exception('删除权限关联资源出错');
        }
        $this->commit();
        return true;
    }
}