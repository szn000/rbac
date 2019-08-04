<?php
namespace sunzhaonan\rbac\model;

use think\Model;

class Base extends Model
{
    protected $connection = '';

    public function __construct($db = '', $data = [])
    {
        parent::__construct($data);
        $this->connection = $db;
    }

}