<?php
namespace lgoods\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class Goods extends ActiveRecord{

    public static function tableName(){
        return "{{%goods}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'g_created_at',
                'updatedAtAttribute' => 'g_updated_at'
            ]
        ];
    }

    public function rules(){
        return [
            [
                [
                    'g_name',
                    'g_sid',
                    'g_stype',
                ], 'safe'
            ]
        ];
    }



}