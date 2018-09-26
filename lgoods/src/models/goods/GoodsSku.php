<?php
namespace lgoods\models\goods;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class GoodsSku extends ActiveRecord{

    public static function tableName(){
        return "{{%goods_sku}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'sku_created_at',
                'updatedAtAttribute' => 'sku_updated_at'
            ]
        ];
    }

    public function rules(){
        return [
            [
                [
                    'sku_g_id',
                    'sku_index',
                    'sku_name',
                    'sku_price',
                ], 'safe'
            ]
        ];
    }

}