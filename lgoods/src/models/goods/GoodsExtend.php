<?php
namespace lgoods\models\goods;


use yii\db\ActiveRecord;


class GoodsExtend extends ActiveRecord{
    public static function tableName(){
        return "{{%goods_extend}}";
    }
    public function rules(){
        return [
            ['g_m_img_id', 'required']
        ];
    }

}