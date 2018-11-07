<?php

use yii\db\Migration;

/**
 * Class m181102_063728_option
 */
class m181102_063728_option extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\attr\Option::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `opt_id` int(10) unsigned not null auto_increment comment '主键',
          `opt_name` VARCHAR(30) not null comment '属性值名称',
          `opt_value` VARCHAR(30) not null comment '属性值索引',
          `opt_attr_id` int(10) unsigned not null comment '属性id',
          `opt_img_value` VARCHAR(255) not null default '' comment '选项值图像id',
          `opt_object_id` int(10) unsigned not null comment '所属对象id',
          `opt_object_type` smallint(3) unsigned not null comment '所属对象类型',
          `opt_created_at` int(10) unsigned not null comment '创建时间',
          `opt_updated_at` int(10) unsigned not null comment '更新时间',
          primary key (`opt_id`),
          index (`opt_attr_id`, `opt_object_id`, `opt_object_type`)
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
