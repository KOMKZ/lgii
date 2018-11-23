<?php
namespace lgoods\models\order;

use lgoods\models\goods\GoodsExtend;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Order extends ActiveRecord{

    CONST PS_NOT_PAY = 0;

    CONST PS_PAID = 1;

    const EVENT_AFTER_PAID = 'order_paid';

    public static function tableName(){
        return "{{%orders}}";
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

    public function getOrder_goods_list(){

        return $this->hasMany(OrderGoods::className(), [
            'og_od_id' => 'od_id'
        ])
            ->from(["og" => OrderGoods::tableName()])
            ->leftJoin(['oe' => GoodsExtend::tableName()], "oe.g_id = og.og_g_id")
            ->select([
                "og_od_id",
                "og_g_id",
                "og_name",
                "oe.g_m_img_id",
            ])
            ;
    }

}