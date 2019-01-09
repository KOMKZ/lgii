<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 下午2:04
 */
namespace lsite\models\action;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ActionLog extends ActiveRecord{



    public static function tableName(){
        return "{{%action_log}}";
    }
    public function rules(){
        return [

        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'al_created_at',
                'updatedAtAttribute' => 'al_updated_at'
            ]
        ];
    }
}