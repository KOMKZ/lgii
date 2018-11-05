<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\attr;

use yii\db\ActiveRecord;

class OCMap extends ActiveRecord{
    public static function tableName(){
        return "{{%object_collect_map}}";
    }

    public function rules(){
        return [
            ['ac_id', 'required']
            ,['ocm_object_id', 'required']
            ,['ocm_object_type', 'required']
        ];
    }

}