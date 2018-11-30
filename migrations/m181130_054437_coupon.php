<?php

use yii\db\Migration;

/**
 * Class m181130_054437_coupon
 */
class m181130_054437_coupon extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\coupon\Coupon::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `coup_id` int(10) unsigned not null auto_increment comment '主键',
            `coup_name` VARCHAR(64) not null DEFAULT '' comment '优惠券名称',
            `coup_caculate_type` smallint(3) unsigned not null comment '优惠券计算模型',
            `coup_caculate_params` VARCHAR(255) not null comment '优惠券计算模型参数',
            `coup_object_id` int(10) unsigned not null comment '优惠券作用对象id',
            `coup_object_type` smallint(3) unsigned not null comment '优惠券作用对象类型',
            `coup_limit_params` varchar(255) not null default '' comment '优惠券限制使用参数',
            `coup_created_at` int(10) unsigned not null comment '创建时间',
            `coup_updated_at` int(10) unsigned not null comment '更新时间',
            `coup_start_at` int(10) unsigned not null comment '开始时间',
            `coup_end_at` int(10) unsigned not null comment '结束时间',
            `coup_status` smallint(3) unsigned not null comment '状态',
            `coup_usage_intro` varchar(255) not null default '' comment '使用说明',
            primary key (`coup_id`)
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
