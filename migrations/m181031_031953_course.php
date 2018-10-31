<?php

use yii\db\Migration;
use app\models\course\Course;
/**
 * Class m181031_031953_course
 */
class m181031_031953_course extends Migration
{
    public function getTableName(){
        return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, Course::tableName()));
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
            `course_id` int(10) unsigned not null auto_increment comment '主键',
            `course_title` VARCHAR(255) not null comment '课程标题',
            `module` char(6) not null comment '所属模块',
            `course_created_at` int(10) unsigned not null comment '创建时间',
            `course_updated_at` int(10) unsigned not null comment '更新时间',
            primary key (`course_id`)
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
