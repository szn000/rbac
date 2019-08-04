<?php


namespace sunzhaonan\rbac\model;

use think\Db;
use think\Exception;

class Role extends Base
{

    public function saveRole($data = [])
    {

    }

    public function delRole($condition)
    {
        $where = [];
        if (is_array($condition)) {
            $where[] = ['id', 'IN', $condition];
        } else {
            $id = (int)$condition;
            if (is_numeric($id) && $id > 0) {
                $where[] = ['id', '=', $condition];
            } else {
                throw new Exception("删除条件错误");
            }
        }
        $this->startTrans();

        $this->commit();
        return true;

    }
}