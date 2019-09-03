<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class ManagerRole extends Base
{

    public function getManagerRole($condition)
    {
        $model = Db::name('manager')->setConnection($this->getConnection());
        $model->leftJoin('manager_role','manager.id=manager_role.manager_id');
        $model->field(['role_id','manager_id']);
        if (is_numeric($condition)) {
            return $model->where('id', $condition)->find();
        } else {
            return $model->where($condition)->select();
        }
    }
    
}