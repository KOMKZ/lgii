<?php

use yii\db\Migration;

/**
 * Class m181127_060729_banner
 */
class m181127_060729_banner extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, \lsite\models\banner\Banner::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `b_id` int(10) unsigned not null auto_increment comment '主键',
          `b_img_id` VARCHAR(255) not null comment '图片id',
          `b_img_app` SMALLINT(3) unsigned not null comment '应用id',
          `b_img_module` SMALLINT(3) unsigned not null comment '模块id',
          `b_reffer_link` varchar(255) not null default '' comment '参考链接',
          `b_reffer_label` varchar(255) not null default '' comment '参考链接label',
          `b_created_at` int(10) unsigned not null comment '创建时间',
          `b_updated_at` int(10) unsigned not null comment '更新时间',
          `b_status` smallint(3) unsigned not null comment '状态',
          primary key (`b_id`),
          index (`b_img_app`, `b_img_module`, `b_status`)
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
