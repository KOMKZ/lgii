<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\attr;

use yii\db\ActiveRecord;

class ACMap extends ActiveRecord{
    public static function tableName(){
        return "{{%attr_collect_map}}";
    }



}