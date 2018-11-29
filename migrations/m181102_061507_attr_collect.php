<?php

use yii\db\Migration;

/**
 * Class m181102_061507_attr_collect
 */
class m181102_061507_attr_collect extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\attr\AttrCollect::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `ac_id` int(10) unsigned not null auto_increment comment '主键',
          `ac_name` VARCHAR(64) not null comment '属性集名称',
          `ac_created_at` int(10) unsigned not null comment '创建时间',
          `ac_updated_at` int(10) unsigned not null comment '更新时间',
          primary key (`ac_id`)
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
