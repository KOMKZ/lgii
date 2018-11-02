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

class Attr extends ActiveRecord{
    public static function tableName(){
        return "{{%attr}}";
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'a_created_at',
                'updatedAtAttribute' => 'a_updated_at'
            ]
        ];
    }

    public function rules(){
        return [
            ["a_name", 'required']
        ];
    }

}