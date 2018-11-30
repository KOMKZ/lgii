<?php

use yii\db\Migration;

/**
 * Class m181130_054215_user_coupon
 */
class m181130_054215_user_coupon extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\coupon\UserCoupon::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `ucou_id` int(10) unsigned not null auto_increment comment '主键',
            `ucou_u_id` int(10) unsigned not null comment '所属用户id',
            `coup_id` int(10) unsigned not null comment '优惠券id',
            `ucou_status` smallint(3) unsigned not null comment '状态',
            `ucou_created_at` int(10) unsigned not null comment '领取时间',
            primary key (`ucou_id`),
            index (`ucou_u_id`, `ucou_status`)
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
