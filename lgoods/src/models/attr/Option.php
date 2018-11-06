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

    CONST OBJECT_TYPE_GOODS = 1;


    public static function tableName(){
        return "{{%option}}";
    }

    public function rules(){
        return [
            ['opt_name', 'required'],
            ['opt_attr_id', 'required'],
            ['opt_value', 'safe']
        ];
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


    public function scenarios(){
        return [
            'default' => [
                'opt_name',
                'opt_attr_id',
                'opt_value',
            ],
            'update' => [
                'opt_name',
            ]
        ];
    }

}