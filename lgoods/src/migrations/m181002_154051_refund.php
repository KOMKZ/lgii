<?php

use yii\db\Migration;
use lgoods\models\refund\RfApplication;

/**
 * Class m181002_154051_refund
 */
class m181002_154051_refund extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, RfApplication::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `rf_id` int(10) unsigned not null auto_increment comment '主键',
            `rf_order_id` int(10) unsigned not null comment '订单id',
            `rf_order_num` varchar(255) not null comment '订单编号',
            `rf_order_third_num` varchar(255) not null comment '订单第三方交易号',
            `rf_third_num` varchar(255) not null default '' comment '退款单第三方交易号',
            `rf_status` char(6) not null comment '退款状态',
            `rf_fee` int(10) unsigned not null comment '退款金额',
            `rf_trans_id` int(10) unsigned not null default 0 comment '交易id',
            `rf_ori_pay_type` char(6) not null comment '订单原支付方式', 
            `rf_succ_at` int(10) unsigned not null default 0 comment '成功退款时间',
            `rf_created_at` int(10) unsigned not null comment '创建时间',
            `rf_updated_at` int(10) unsigned not null comment '更新时间',
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
