<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\cart;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class CartItem extends ActiveRecord{
    const STATUS_VALID = 1;
    const STATUS_DELETE = 2;

    public static function tableName(){
        return "{{%cart_item}}";
    }
    public function rules(){
        return [
            ['ci_sku_id', 'required'],
            ['ci_belong_uid', 'required'],
            ['ci_amount', 'required']
        ];
    }
    public function scenarios(){
        return [
            'default' => [
                'ci_sku_id', 'ci_belong_uid', 'ci_amount'
            ],
            'update' => [
                'ci_amount'
            ]
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'ci_created_at',
                'updatedAtAttribute' => 'ci_updated_at'
            ]
        ];
    }


}