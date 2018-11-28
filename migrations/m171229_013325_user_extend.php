<?php

use yii\db\Migration;
use luser\models\user\UserExtend;
class m171229_013325_user_extend extends Migration
{
	public function getTableName(){
		return preg_replace('/[\{\}]/', '', preg_replace("/%/", Yii::$app->db->tablePrefix, UserExtend::tableName()));
	}
	public function safeUp(){
		$tableName = $this->getTableName();
		$createTabelSql = "
		CREATE TABLE `{$tableName}` (
			`u_ext_id` int(10) unsigned not null AUTO_INCREMENT COMMENT '数据id',
			`u_id` int(10) unsigned not null COMMENT '关联用户id',
			`u_avatar_id1` varchar(64) not null default '' comment '头像文件id_01',
			`u_avatar_id2` varchar(64) not null default '' comment '头像文件id_02',
			`u_ext_created_at` int(10) unsigned not null comment '创建时间',
            `u_ext_updated_at` int(10) unsigned not null comment '更新时间',
			primary key (`u_ext_id`),
			index (`u_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户扩展信息'
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
