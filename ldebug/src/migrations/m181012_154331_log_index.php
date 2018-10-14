<?php

use yii\db\Migration;

/**
 * Class m181012_154331_log_index
 */
class m181012_154331_log_index extends Migration
{
    public function getTableName(){
        return "log_index";
    }
    public function safeUp(){
        $tableName = $this->getTableName();
        $createTabelSql = "
        create table `{$tableName}`(
          `id` bigint(21) unsigned not null auto_increment  comment '',
          `tag` varchar(64) not null comment '',
          `url` text not null comment '',
          `ajax` smallint(3) unsigned not null comment '',
          `method` char(10) not null comment '',
          `ip` varchar(64) not null comment '',
          `time` int(10) unsigned not null comment '',
          `statusCode` char(4) not null comment '',
          `sqlCount` int(10) unsigned not null comment '',
          `mailCount` int(10) unsigned not null comment '',
          `mailFiles` text not null comment '',
          index (`tag`),
          index (`time`),
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
