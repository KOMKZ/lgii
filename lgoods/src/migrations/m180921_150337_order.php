<?php

use yii\db\Migration;
use lgoods\models\order\Order;

/**
 * Class m180921_150337_order
 */
class m180921_150337_order extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, Order::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `od_id` int(10) unsigned not null auto_increment comment '主键',
            `od_pid` int(10) unsigned not null default 0 comment '父订单id',
            `od_num` varchar(255) not null comment '订单编号',
            `od_title` varchar(255) not null comment '订单标题',
            `od_belong_uid` int(10) unsigned not null default 0 comment '订单所属用户uid',
            `od_price` int(10) unsigned not null default 0 comment '订单价格',
            `od_pay_status` smallint unsigned not null default 0 comment '订单支付状态',
            `od_paid_at` int(10) unsigned not null default 0 comment '支付时间',
            `od_pay_type` char(5) not null default '' comment '支付方式',
            `od_created_at` int(10) unsigned not null comment '创建时间',
            `od_updated_at` int(10) unsigned not null comment '更新时间',
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
