<?php

use yii\db\Migration;

/**
 * Class m181125_070912_cart_item
 */
class m181125_070912_cart_item extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lgoods\models\cart\CartItem::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `ci_id` int(10) unsigned not null auto_increment comment '主键',
            `ci_g_id` int(10) unsigned not null comment '商品id',
            `ci_sku_id` int(10) unsigned not null comment '商品sku的id',
            `ci_amount` int(10) unsigned not null comment '商品购买的数量',
            `ci_belong_uid` int(10) unsigned not null comment '条目所属用户id',
            `ci_created_at` int(10) unsigned not null comment '条目创建时间',
            `ci_updated_at` int(10) unsigned not null comment '条目更新时间',
            `ci_status` smallint(3) unsigned not null comment '条目的状态',
            primary key (`ci_id`)
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
