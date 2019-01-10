<?php

use yii\db\Migration;

/**
 * Class m190108_081417_action_log
 */
class m190108_081417_action_log extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lsite\models\action\ActionLog::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `al_id` int(10) unsigned not null auto_increment comment '主键',
            `al_opr_uid` int(10) unsigned not null default 0 comment '操作用户id',
            `al_action` int(10) not null DEFAULT 0 comment '动作名称',
            `al_obj_id` int(10) unsigned not null DEFAULT 0 comment '动作关联对象id',
            `al_data` text not null comment '关联记录参数',
            `al_created_at` int(10) unsigned not null comment '创建时间',
            `al_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`al_id`),
            index (`al_action`, `al_obj_id`)
        );
        ";
        $this->execute($createTabelSql);
        return true;
    }
    public function safeDown(){
        $tableName = $this->getTableName();
        $dropTableSql = "
        drop table if exists `{$tableName}`
        ";
        $this->execute($dropTableSql);
        return true;
    }
}
