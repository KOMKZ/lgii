<?php
namespace lgoods\models\category;

use Yii;
use yii\db\ActiveRecord;
/**
 *
 */
class GoodsClassification extends ActiveRecord
{


    public static function tableName(){
        return "{{%goods_classification}}";
    }




    public function scenarios(){
        return [
            'default' => [
                'g_cls_name', 'g_cls_show_name', 'g_cls_pid', 'g_cls_img_id'
            ],
            'update' => [
                'g_cls_name', 'g_cls_show_name', 'g_cls_img_id'
            ]
        ];
    }


    public function rules(){
        return [
            ['g_cls_name', 'string'],
            ['g_cls_name', 'required'],

            ['g_cls_img_id', 'string'],

            ['g_cls_show_name', 'string'],
            ['g_cls_show_name', 'default', 'value' => function(){return $this->g_cls_name;}],

            ['g_cls_pid', 'default', 'value' => 0],
            ['g_cls_pid', 'integer'],
            // todo
            // ['g_cls_pid', 'exist', 'targetAttribute' => 'g_cls_id'],



        ];
    }




}
