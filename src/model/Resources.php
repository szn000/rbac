<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class Resources
{
    public function saveResources($permissionIds = '', $data = [])
    {
        if (!empty($data)) {
            $this->data($data);
        }
        $validate = new \sunzhaonan\rbac\validate\Resources();
        if (!$validate->check($this)) {
            throw new Exception($validate->getError());
        }
        $data = $this->getData();
        if (isset($data['id']) && !empty($data['id'])) {
            $this->isUpdate(true);
        }
        $this->save();
        return $this;
    }

    public function getResources($condition)
    {
        $model = Db::name('resources')->setConnection($this->getConnection());
        if (is_numeric($condition)) {
            return $model->where('id', $condition)->find();
        } else {
            return $model->where($condition)->select();
        }
    }

    public function delResources($id)
    {
        $where = [];
        $relationWhere = [];
        if (is_array($id)) {
            $where[] = ['id', 'IN', $id];
            $relationWhere[] = ['resources_id', 'IN', $id];
        } else {
            $id = (int)$id;
            if (is_numeric($id) && $id > 0) {
                $where[] = ['id', '=', $id];
                $relationWhere[] = ['resources_id', '=', $id];
            } else {
                throw new Exception('删除条件错误');
            }
        }

        $this->startTrans();
        if ($this->where($where)->delete() === false) {
            throw new Exception('删除资源出错');
        }
        $permissionResources = new PermissionResources($this->connection);
        if ($permissionResources->where($relationWhere)->delete() === false) {
            $this->rollback();
            throw new Exception('删除权限关联资源出错');
        }
        $this->commit();
        return true;
    }

    public function getResourcesByManagerId($manager_id)
    {

    }

}
