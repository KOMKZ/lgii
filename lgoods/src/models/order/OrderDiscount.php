<?php
namespace lgoods\models\order;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class OrderDiscount extends ActiveRecord{

    public static function tableName(){
        return "{{%order_discount}}";
    }



}