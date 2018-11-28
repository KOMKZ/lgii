<?php
namespace luser\models\user;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 *
 */
class UserData extends ActiveRecord
{
	public static function tableName(){
		return "{{%user_data}}";
	}
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'u_data_created_at',
				'updatedAtAttribute' => false
			]
		];
	}
	public function rules(){
		return [
			['u_remain_time', 'required']
			,['u_remain_time', 'integer']

			,['u_last_timestamp', 'required']
			,['u_last_timestamp', 'integer']
		];
	}
}
