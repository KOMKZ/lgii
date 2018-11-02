<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\attr;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Option extends ActiveRecord{
    public static function tableName(){
        return "{{%option}}";
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'opt_created_at',
                'updatedAtAttribute' => 'opt_updated_at'
            ]
        ];
    }

}