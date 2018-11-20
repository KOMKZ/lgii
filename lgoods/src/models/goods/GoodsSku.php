<?php
namespace lgoods\models\goods;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class GoodsSku extends ActiveRecord{

    CONST INDEX_STATUS_VALID = 1;
    CONST INDEX_STATUS_INVALID = 2;

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
                    'sku_is_master'
                ], 'safe'
            ]
        ];
    }

}