<?php

use yii\db\Migration;
use lgoods\models\goods\GoodsSku;
/**
 * Class m180921_030038_goods_sku
 */
class m180921_030038_goods_sku extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, GoodsSku::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `sku_id` int(10) unsigned not null auto_increment comment '主键',
            `sku_g_id` int(10) unsigned not null comment '商品id',
            `sku_index` VARCHAR(255) not null comment 'sku索引',
            `sku_index_status` smallint(3) not null comment 'sku索引的状态',
            `sku_is_master` smallint(3) unsigned not null comment '是否是首选sku',
            `sku_name` VARCHAR(255) not null default '' comment 'sku名称',
            `sku_price` int(10) unsigned not null comment 'sku的价格',
            `sku_created_at` int(10) unsigned not null comment '创建时间',
            `sku_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`sku_id`),
            unique (`sku_g_id`, `sku_index`)
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
