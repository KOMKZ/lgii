<?php

use yii\db\Migration;

/**
 * Class m181102_055844_attr
 */
class m181102_055844_attr extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\attr\Attr::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `a_id` int(10) unsigned not null auto_increment comment '主键',
          `a_name` VARCHAR(20) not null comment '属性名称',
          `a_created_at` int(10) unsigned not null comment '创建时间',
          `a_updated_at` int(10) unsigned not null comment '更新时间',
          primary key (`a_id`)
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
