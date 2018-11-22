<?php

use yii\db\Migration;

/**
 * Class m181102_062213_attr_collect_map
 */
class m181102_062213_attr_collect_map extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\attr\ACMap::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `ac_id` int(10) unsigned not null comment '主键',
          `a_id` int(10) unsigned not null comment '主键',
          primary key (`a_id`, `ac_id`)
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
