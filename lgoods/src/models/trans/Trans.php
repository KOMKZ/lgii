<?php
namespace lgoods\models\trans;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Trans extends ActiveRecord{

    CONST TRADE_ORDER = 1;

    CONST TRADE_TRANS = 2;

    CONST TRADE_REFUND = 3;

    

    CONST TPS_NOT_PAY = 0;

    CONST TPS_PAID = 1;

    const EVENT_AFTER_PAYED = 'trans_paid';

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