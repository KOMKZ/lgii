<?php

use yii\db\Migration;

/**
 * Class m181122_100658_sale_rule
 */
class m181122_100658_sale_rule extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\sale\SaleRule::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `sr_id` int(10) unsigned not null comment '主键',
            `sr_name` VARCHAR(64) not null DEFAULT '' comment '规则名称',
            `sr_caculate_type` smallint(3) unsigned not null comment '规则计算模型',
            `sr_caculate_params` VARCHAR(255) not null comment '规则计算模型参数',
            `sr_object_id` int(10) unsigned not null comment '规则作用对象id',
            `sr_object_type` smallint(3) unsigned not null comment '规则作用对象类型',
            `sr_created_at` int(10) unsigned not null comment '创建时间',
            `sr_updated_at` int(10) unsigned not null comment '更新时间',
            `sr_start_at` int(10) unsigned not null comment '开始时间',
            `sr_end_at` int(10) unsigned not null comment '结束时间',
            `sr_status` smallint(3) unsigned not null comment '状态',
            `sr_usage_intro` varchar(255) not null default '' comment '使用说明',
            primary key (`sr_id`),
            index (`sr_object_id`, `sr_object_type`, `sr_status`, `sr_start_at`, `sr_end_at`)
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
