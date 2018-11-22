<?php
use yii\db\Migration;
use lfile\models\ar\File;

class m170713_140045_file extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, File::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `file_id` int(10) unsigned not null auto_increment comment '主键',
            `file_save_name` varchar(255) not null default '' comment '下载名称',
            `file_real_name` char(50) not null comment '文件真实名称，不包含后缀',
            `file_save_type` char(10) not null comment '存储媒介类型',
            `file_is_tmp` smallint(3) unsigned not null comment '文件时效性',
            `file_valid_time` int(10) unsigned not null comment '文件有效时间',
            `file_is_private` smallint(3) unsigned not null comment '文件访问属性',
            `file_category` varchar(255) not null comment '文件分类',
            `file_prefix` char(50) not null comment '文件存储前缀',
            `file_md5_value` char(50) not null comment '文件md5值',
            `file_medium_info` text null comment '文件存储媒介信息',
            `file_ext` char(10) not null default '' comment '文件后缀',
            `file_mime_type` varchar(64) not null default '' comment '文件媒体类型',
            `file_created_time` int(10) unsigned not null comment '文件创建时间',
            primary key `file_id` (file_id),
            index `query_id` (file_prefix, file_real_name, file_ext),
            index `md5_value` (file_md5_value)
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
