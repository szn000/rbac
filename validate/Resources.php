<?php
namespace sunzhaonan\rbac\validate;


use think\Validate;

class Resources extends Validate
{
    protected $rule = [
        'name' => 'require|max:50|unique:sunzhaonan\rbac\model\resources,name^id'
    ];

    protected $message = [
        'name.require' => '角色名不能为空',
        'name.max' => '角色名不能长于50个字符',
        'name.unique' => '角色名称不能重复'
    ];

}
