<?php

use yii\db\Migration;
use lgoods\models\refund\RfGoods;


/**
 * Class m181003_090510_refund_goods
 */
class m181003_090510_refund_goods extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, RfGoods::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `rg_id` int(10) unsigned not null auto_increment comment '主键',
            `rg_og_id` int(10) unsigned not null comment '订单商品id',
            `rg_rf_id` int(10) unsigned not null default 0 comment '退款单id',
            `rg_total_num` int(10) unsigned not null default 0 comment '条目数量',
            `rg_single_price` int(10) unsigned not null default 0 comment '条目单价',
            `rg_total_price` int(10) unsigned not null default 0 comment '条目总结',
            `rg_name` varchar(255) not null comment '条目名称',
            `rg_g_id` int(10) unsigned not null comment '商品id',
            `rg_g_sid` int(10) unsigned not null comment '商品对象id',
            `rg_g_stype` char(5) not null comment '商品对象模块',
            `rg_sku_id` int(10) unsigned not null comment 'sku主建id',
            `rg_sku_index` varchar(255) not null comment 'sku索引名称',
            `rg_created_at` int(10) unsigned not null comment '创建时间',
            `rg_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`rg_id`),
            index (`rg_rf_id`)
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
