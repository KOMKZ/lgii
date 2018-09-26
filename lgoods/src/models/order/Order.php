<?php
namespace lgoods\models\order;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Order extends ActiveRecord{

    CONST PS_NOT_PAY = 0;

    CONST PS_PAID = 1;

    public static function tableName(){
        return "{{%order}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'od_created_at',
                'updatedAtAttribute' => 'od_updated_at'
            ]
        ];
    }

}