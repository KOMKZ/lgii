<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-30
 * Time: 下午1:45
 */
namespace lgoods\models\coupon;

use lgoods\caculators\Discount;
use lgoods\caculators\FullSub;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Coupon extends  ActiveRecord{





    public function check($params){
        // todo
        return true;
    }
    public function apply($params = []){
        $caculator = null;
        switch ($this->coup_caculate_type){
//            case Discount::ID:
//                $caculator = new Discount();
//                break;
            case FullSub::ID:
                $caculator = new FullSub();
                break;
            default:
                throw new \Exception("未知的计算类型");
        }

        return $caculator->caculate(['sr_caculate_params' => $this->coup_caculate_params]);
    }
    public static function tableName(){
        return "{{%coupon}}";
    }
    public function rules(){
        return [
            ['coup_limit_params', 'default', 'value' => ''],
            ['coup_usage_intro', 'default', 'value' => ''],
            ['coup_name', 'required'],
            ['coup_caculate_type', 'required'],
            ['coup_caculate_params', 'required'],
            ['coup_object_id', 'required'],
            ['coup_object_type', 'required'],
            ['coup_limit_params', 'default', 'value' => ''],
            ['coup_start_at', 'required'],
            ['coup_end_at', 'required'],
            ['coup_name', 'required'],

        ];
    }
    public function scenarios(){
        return [
            'default' => [
                'coup_name',
                'coup_caculate_type',
                'coup_caculate_params',
                'coup_object_id',
                'coup_object_type',
                'coup_limit_params',
                'coup_start_at',
                'coup_end_at',
                'coup_usage_intro',
            ],
            'update' => [
                'coup_name',
                "coup_usage_intro",
                "coup_status"
            ]
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'coup_created_at',
                'updatedAtAttribute' => 'coup_updated_at'
            ]
        ];
    }
}