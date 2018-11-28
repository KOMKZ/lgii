<?php

use yii\db\Migration;
use luser\models\user\UserData;

class m171221_014152_user_data extends Migration
{
	public function getTableName(){
		return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, UserData::tableName()));
	}
	public function safeUp(){
		$tableName = $this->getTableName();
		$createTabelSql = "
		CREATE TABLE `{$tableName}` (
			`u_data_id` int(10) unsigned not null AUTO_INCREMENT COMMENT '数据id',
			`u_id` int(10) unsigned not null COMMENT '关联用户id',
			`u_last_timestamp` int(10) unsigned not null comment '上次访问的时间',
			`u_remain_time` int(10) unsigned not null comment '剩余的访问次数',
			`u_data_created_at` int(10) unsigned not null comment '创建时间',
			primary key (`u_data_id`),
			index (`u_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户统计数据表'
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
