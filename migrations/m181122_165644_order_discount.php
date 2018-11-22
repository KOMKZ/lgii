<?php

use yii\db\Migration;

/**
 * Class m181122_165644_order_discount
 */
class m181122_165644_order_discount extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\order\OrderDiscount::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `od_id` int(10) unsigned not null comment '主键',
            `od_discount_items` text not null comment '订单折扣',
            `od_discount_des` text not null comment '订单折扣描述',
            primary key (`od_id`)
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
