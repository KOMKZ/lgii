<?php

use yii\db\Migration;
use lgoods\models\Goods;

/**
 * Class m180921_032306_goods
 */
class m180921_032306_goods extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, Goods::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `g_id` int(10) unsigned not null auto_increment comment '主键',
            `g_name` VARCHAR(255) not null comment '商品名称',
            `g_sid` int(10) unsigned not null comment '对象id',
            `g_stype` char(5) not null comment '对象模块',
            `g_created_at` int(10) unsigned not null comment '创建时间',
            `g_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`g_id`),
            index (`g_sid`, `g_stype`)
        );
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
