<?php

use yii\db\Migration;
use lgoods\models\trans\Trans;

/**
 * Class m180923_110913_trans
 */
class m180923_110913_trans extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, Trans::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `trs_id` int(10) unsigned not null auto_increment comment '主键',
            `trs_title` varchar(255) not null comment '交易标题',
            `trs_type` smallint(3) unsigned not null comment '交易类型',
            `trs_target_id` int(10) unsigned not null comment '对象id',
            `trs_num` varchar(255) not null comment '平台交易流水号码',
            `trs_target_num` varchar(255) not null comment '平台交易对象流水号码',
            `trs_fee` int(10) unsigned not null comment '交易价格',
            `trs_pay_status` smallint(3) unsigned not null default 0 comment '支付状态',
            `trs_pay_at` int(10) unsigned not null default 0 comment '支付状态',
            `trs_pay_type` char(5) not null default '' comment '支付方式',
            `trs_pay_num` varchar(255) not null default '' comment '第三方支付交易号',
            `trs_content` text not null comment '交易号码',
            `trs_timeout` int(10) unsigned not null default 0 comment '失效时间',
            `trs_created_at` int(10) unsigned not null comment '创建时间',
            `trs_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`trs_id`)
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
