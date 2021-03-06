<?php
namespace lgoods\models\goods;

use lgoods\models\attr\ACMap;
use lgoods\models\attr\Attr;
use lgoods\models\attr\AttrEnum;
use lgoods\models\attr\OCMap;
use lgoods\models\attr\Option;
use lsite\models\action\ActionTargetInterface;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class Goods extends ActiveRecord implements ActionTargetInterface{

    public function getLogId()
    {
        return $this->g_id;
    }

    public function getLogParams($name)
    {
        return [
            'g_id' => $this->g_id
        ];
    }

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


    public function getGoods_extend(){
        return $this->hasOne(GoodsExtend::class, ['g_id' => 'g_id']);
    }

    public function getG_collect(){
        $query = $this->hasOne(OCMap::class, [
            'ocm_object_id' => 'g_id',
        ])
            ->with("c_attrs")
        ->andWhere(['=', 'ocm_object_type', AttrEnum::OPT_OBJECT_TYPE_GOODS])
            ;
        return $query;
    }

    public function getGoods_skus(){
        return $this->hasMany(GoodsSku::class, [
            'sku_g_id' => 'g_id',

        ])
            ->orderBy(['sku_is_master' => SORT_DESC]);
    }
    public function rules(){
        return [
            [
                [
                    'g_name',
                    'g_sid',
                    'g_stype',
                    'g_cls_id'
                ], 'safe'
            ]
        ];
    }



}