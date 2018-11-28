<?php

use yii\db\Migration;
use luser\models\user\User;

class m170810_083410_user extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, User::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `u_id` int(10) unsigned not null auto_increment comment '主键',
            `u_username` varchar(255) not null comment '用户名称',
            `u_auth_key` varchar(255) not null comment '用户校验key',
            `u_password_hash` varchar(255) not null comment '用户密码hash值',
            `u_password_reset_token` varchar(255) not null comment '用户重设密码token',
            `u_access_token` varchar(255) not null default '' comment '用户访问token',
            `u_email` varchar(255) not null comment '用户邮件',
            `u_status` char(12) not null comment '用户状态',
            `u_auth_status` char(12) not null comment '用户校验状态',
            `u_created_at` integer(10) not null comment '创建时间',
            `u_updated_at` integer(10) not null comment '更新时间',
            primary key (u_id)
        )CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;
        ";
        $this->execute($createTabelSql);
        return true;
    }
    public function safeDown(){
        $tableName = $this->getTableName();
        $dropTableSql = "
        drop table if exists {$tableName}
        ";
        $this->execute($dropTableSql);
        return true;
    }



}
