SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `###manager`;
CREATE TABLE `manager` (
        `id` int(11) NOT NULL COMMENT '用户ID',
        `department_id` int(11) NOT NULL,
        `username` varchar(255) NOT NULL COMMENT '用户名',
        `password` varchar(255) NOT NULL COMMENT '密码',
        `status` int(2) NOT NULL DEFAULT '0' COMMENT '状态',
        `is_supper` tinyint(1) DEFAULT '0',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
        `real_name` varchar(50) DEFAULT NULL COMMENT '用户真实姓名'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###manager_role`;
CREATE TABLE `manager_role` (
        `manager_id` int(11) NOT NULL,
        `role_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###menu`;

CREATE TABLE `menu` (
        `id` int(11) NOT NULL,
        `path` varchar(100) NOT NULL,
        `title` varchar(100) NOT NULL,
        `icon` varchar(100) DEFAULT NULL,
        `pid` int(11) NOT NULL DEFAULT '0',
        `permission_id` int(11) DEFAULT '0',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###permission`;
CREATE TABLE `permission` (
        `id` int(11) NOT NULL,
        `name` varchar(100) NOT NULL,
        `decription` varchar(255) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###permission_resources`;
CREATE TABLE `permission_resources` (
        `permission_id` int(11) NOT NULL,
        `resources_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###resources`;
CREATE TABLE `resources` (
        `id` int(11) NOT NULL,
        `name` varchar(50) NOT NULL COMMENT '资源名',
        `description` varchar(255) NOT NULL COMMENT '资源说明',
        `type` varchar(20) NOT NULL COMMENT '请求类型',
        `module` varchar(50) NOT NULL COMMENT '请求控制器',
        `operate` varchar(50) NOT NULL COMMENT '请求方法',
        `status` int(11) NOT NULL DEFAULT '1' COMMENT '状态',
        `is_public` int(11) NOT NULL DEFAULT '0' COMMENT '是否无需权限验证 0需要 1不需要 默认0',
        `is_super` int(11) NOT NULL DEFAULT '0' COMMENT '是否超管专有 0不是 1是 默认0',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###role`;
CREATE TABLE `role` (
        `id` int(11) NOT NULL,
        `name` varchar(50) NOT NULL,
        `status` int(11) NOT NULL DEFAULT '1' COMMENT '是否启用',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `###role_permission`;
CREATE TABLE `role_permission` (
        `role_id` int(11) NOT NULL,
        `permission_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `manager` ADD PRIMARY KEY (`id`);
ALTER TABLE `manager_role` ADD PRIMARY KEY (`manager_id`,`role_id`);
ALTER TABLE `menu` ADD PRIMARY KEY (`id`);
ALTER TABLE `permission` ADD PRIMARY KEY (`id`);
ALTER TABLE `permission_resources` ADD PRIMARY KEY (`permission_id`,`resources_id`);
ALTER TABLE `role` ADD PRIMARY KEY (`id`);
ALTER TABLE `role_permission` ADD PRIMARY KEY (`role_id`,`permission_id`);
ALTER TABLE `resources` ADD PRIMARY KEY (`id`);
ALTER TABLE `manager` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID';
ALTER TABLE `menu` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `permission` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `role` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `resources` MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT
