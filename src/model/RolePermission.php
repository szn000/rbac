<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class RolePermission extends Base
{

    public function getRolePermission($condition)
    {
        $model = Db::name('role')->setConnection($this->getConnection());
        $model->leftJoin('role_permission','role.id=role_permission.role_id');
        $model->field(['role_id','permission_id']);
        if (is_numeric($condition)) {
            return $model->where('id', $condition)->find();
        } else {
            return $model->where($condition)->select();
        }
    }
}