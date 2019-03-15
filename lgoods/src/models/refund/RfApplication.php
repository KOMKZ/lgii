<?php
namespace lgoods\models\refund;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class RfApplication extends ActiveRecord{

    public static function tableName(){
        return "{{%refund}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'rf_created_at',
                'updatedAtAttribute' => 'rf_updated_at'
            ]
        ];
    }

}