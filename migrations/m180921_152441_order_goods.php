<?php

use yii\db\Migration;
use lgoods\models\order\OrderGoods;
/**
 * Class m180921_152441_order_goods
 */
class m180921_152441_order_goods extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, OrderGoods::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `og_id` int(10) unsigned not null auto_increment comment '主键',
            `og_od_id` int(10) unsigned not null default 0 comment '订单id',
            `og_total_num` int(10) unsigned not null default 0 comment '条目数量',
            `og_single_price` int(10) unsigned not null default 0 comment '条目单价',
            `og_total_price` int(10) unsigned not null default 0 comment '条目总结',
            `og_name` varchar(255) not null comment '条目名称',
            `og_g_id` int(10) unsigned not null comment '商品id',
            `og_g_sid` int(10) unsigned not null comment '商品对象id',
            `og_g_stype` char(5) not null comment '商品对象模块',
            `og_sku_id` int(10) unsigned not null comment 'sku主建id',
            `og_sku_index` varchar(255) not null comment 'sku索引名称',
            `og_discount_items` text not null comment '商品折扣条目信息',
            `og_discount_des` text not null comment '商品折扣条目信息描述',
            `og_created_at` int(10) unsigned not null comment '创建时间',
            `og_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`og_id`),
            index (`og_od_id`),
            index (`og_g_id`)
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
