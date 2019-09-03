<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class PermissionResources extends Base
{
    
    public function getPermissionResources($condition)
    {
        $model = Db::name('permission')->setConnection($this->getConnection());
        $model->leftJoin('permission_resources','permission.id=permission_resources.permission_id');
        $model->field(['resources_id','permission_id']);
        if (is_numeric($condition)) {
            return $model->where('id', $condition)->find();
        } else {
            return $model->where($condition)->select();
        }
    }

}