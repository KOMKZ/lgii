<?php
namespace lgoods\models\trans;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

class PayTrace extends ActiveRecord{






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
                TransEnum::PT_TYPE_DATA,
                TransEnum::PT_TYPE_URL
            ]]

            ,['pt_pay_status', 'in', 'range' => [
                TransEnum::PT_PAY_STATUS_PAYED,
                TransEnum::PT_PAY_STATUS_NOPAY
            ]]

            ,['pt_status', 'in', 'range' => [
                TransEnum::PT_STATUS_INIT,
                TransEnum::PT_STATUS_CANCEL,
                TransEnum::PT_STATUS_PAYED,
                TransEnum::PT_STATUS_ERROR
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