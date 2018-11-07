<?php

use yii\db\Migration;
use lgoods\models\goods\GoodsExtend;

/**
 * Class m181107_081803_goods_extend
 */
class m181107_081803_goods_extend extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, GoodsExtend::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `g_id` int(10) unsigned not null comment '主键',
            `g_m_img_id` VARCHAR(255) not null DEFAULT '' comment '商品主图',
            primary key (`g_id`)
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
