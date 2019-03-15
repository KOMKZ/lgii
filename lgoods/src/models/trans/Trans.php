<?php
namespace lgoods\models\trans;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Trans extends ActiveRecord{


    public static function tableName(){
        return "{{%trans}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'trs_created_at',
                'updatedAtAttribute' => 'trs_updated_at'
            ]
        ];
    }

}