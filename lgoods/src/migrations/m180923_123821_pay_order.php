<?php

use yii\db\Migration;
use lgoods\models\trans\PayTrace;
/**
 * Class m180923_123821_pay_order
 */
class m180923_123821_pay_order extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, PayTrace::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `pt_id` int(10) unsigned not null auto_increment comment '支付单id',
            `pt_pay_type` char(12) not null comment '支付类型',
            `pt_pre_order` text null comment '预支付单',
            `pt_pre_order_type` char(12) not null comment '预支付单数据类型',
            `pt_pay_status` char(12) not null comment '支付状态',
            `pt_status` char(12) not null comment '状态',
            `pt_belong_trans_number` char(20) not null comment '所属交易编号',
            `pt_belong_trans_id` int(10) unsigned not null comment '所属交易id',
            `pt_third_data` text null comment '第三方相关数据',
            `pt_timeout` int(10) unsigned not null default 0 comment '失效时间',
            `pt_created_at` int(10) unsigned not null comment '创建时间',
            `pt_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (pt_id)
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
