<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-2
 * Time: 下午2:00
 */
namespace lgoods\models\sale;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class SaleRule extends ActiveRecord{




    public static function tableName(){
        return "{{%sale_rule}}";
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'sale_created_at',
                'updatedAtAttribute' => 'sale_updated_at'
            ]
        ];
    }



}