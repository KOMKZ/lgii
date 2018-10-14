<?php

use yii\db\Migration;

/**
 * Class m181012_154323_log_data
 */
class m181012_154323_log_data extends Migration
{
    public function getTableName(){
        return "log_data";
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `id` bigint(21) unsigned not null auto_increment  comment '',
          `tag` varchar(64) not null comment '',
          `data` MEDIUMTEXT not null comment '',
          index (`tag`),
          primary key (`id`) 
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
