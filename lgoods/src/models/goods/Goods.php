<?php
namespace lgoods\models\goods;

use lgoods\models\attr\ACMap;
use lgoods\models\attr\Attr;
use lgoods\models\attr\OCMap;
use lgoods\models\attr\Option;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class Goods extends ActiveRecord{



    public static function tableName(){
        return "{{%goods}}";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'g_created_at',
                'updatedAtAttribute' => 'g_updated_at'
            ]
        ];
    }

    public function getG_attrs(){
        $attrTable = Attr::tableName();
        $optTable = Option::tableName();
        $query = $this->hasMany(Option::class, [
            'opt_object_id' => 'g_id'
        ])
            ->leftJoin($attrTable, "{$attrTable}.a_id = {$optTable}.opt_attr_id")
            ->select([
                'opt_object_id',
                'opt_object_type',
                'opt_attr_id',
                "{$attrTable}.a_name"
            ])
            ->andWhere(['=', 'opt_object_type', Option::OBJECT_TYPE_GOODS])
            ->groupBy('opt_attr_id')
            ;
        return $query;
    }

    public function getGoods_skus(){
        return $this->hasMany(GoodsSku::class, [
            'sku_g_id' => 'g_id'
        ])->orderBy(['sku_is_master' => SORT_DESC]);
    }
    public function rules(){
        return [
            [
                [
                    'g_name',
                    'g_sid',
                    'g_stype',
                ], 'safe'
            ]
        ];
    }



}