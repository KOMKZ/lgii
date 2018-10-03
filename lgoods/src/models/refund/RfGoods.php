<?php
namespace lgoods\models\refund;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class RfGoods extends ActiveRecord{



    public static function tableName(){
        return "{{%refund_goods}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'rg_created_at',
                'updatedAtAttribute' => 'rg_updated_at'
            ]
        ];
    }

}