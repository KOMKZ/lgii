<?php

use yii\db\Migration;
use lfile\models\ar\FileTask;


class m170718_131208_file_task extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, FileTask::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `file_task_id` int(10) unsigned not null auto_increment comment '主键',
            `file_task_type` char(10) not null comment '文件任务的类型',
            `file_task_code` char(50) not null comment '文件任务的业务号',
            `file_task_start_at` int(10) unsigned not null comment '文件任务的开始时间',
            `file_task_invalid_at` int(10) unsigned null comment '文件任务的失效时间',
            `file_task_completed_at` int(10) unsigned null comment '文件任务的完成时间',
            `file_task_status` char(10) not null comment '文件任务的状态',
            `file_task_data` text null comment '文件任务执行相关数据',
            primary key `file_task_id` (file_task_id),
            index `file_task_code` (file_task_type, file_task_code)
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
