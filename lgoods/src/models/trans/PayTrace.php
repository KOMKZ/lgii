<?php
namespace lgoods\models\trans;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

class PayTrace extends ActiveRecord{

    const STATUS_INIT = 'init';
    const STATUS_CANCEL = 'cancel';
    const STATUS_PAYED = 'payed';
    const STATUS_ERROR = 'error';

    CONST PAY_STATUS_PAYED = 'payed';
    const PAY_STATUS_NOPAY = 'nopay';

    CONST TYPE_DATA = 'data';
    CONST TYPE_URL = 'url';

    CONST EVENT_AFTER_PAYED = "pay_order_payed";
    const EVENT_AFTER_RFED = 'pay_refund';
    
    const EVENT_AFTER_UPDATE = 'afterUpdate';


    public static function tableName(){
        return "{{%pay_trace}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'pt_created_at',
                'updatedAtAttribute' => 'pt_updated_at'
            ]
        ];
    }

    public function setThird_data(Array $value){
        $thirdData = ($thirdData = json_decode($this->pt_third_data, true)) ? $thirdData : [];
        $thirdData = ArrayHelper::merge($thirdData, $value);
        $this->pt_third_data = json_encode($thirdData);
    }

    public function getThird_data(){
        return json_decode($this->pt_third_data, true);
    }

    public function rules(){
        return [
//            ,['pt_pay_type', 'in', 'range' => [

//            ]]

            ['pt_pre_order_type', 'in', 'range' => [
                static::TYPE_DATA,
                static::TYPE_URL
            ]]

            ,['pt_pay_status', 'in', 'range' => [
                static::PAY_STATUS_PAYED,
                static::PAY_STATUS_NOPAY
            ]]

            ,['pt_status', 'in', 'range' => [
                static::STATUS_INIT,
                static::STATUS_CANCEL,
                static::STATUS_PAYED,
                static::STATUS_ERROR
            ]]

            ,['pt_payment_id', 'filter', 'filter' => function($value){
                if($value == "" && 'npay' != $this->pt_pay_type){
                    $this->addError('pt_payment_id', "pt_payment_id当前支付方式下不能为空");
                }
                return $value;
            }]


        ];
    }

}