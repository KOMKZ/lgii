<?php

use yii\db\Migration;

/**
 * Class m181102_064732_object_collect_map
 */
class m181102_064732_object_collect_map extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\attr\OCMap::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `ac_id` int(10) unsigned not null comment '属性集合id',
          `ocm_object_id` int(10) unsigned not null comment '对象id',
          `ocm_object_type` smallint(3) unsigned not null comment '对象类型',
          primary key (`ac_id`, `ocm_object_id`, `ocm_object_type`)
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
