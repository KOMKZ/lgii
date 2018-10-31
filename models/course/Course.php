<?php
namespace app\models\course;

use yii\base\Model;
use lgoods\models\goods\GoodsInterface;
use yii\db\ActiveRecord;

class Course extends ActiveRecord implements GoodsInterface{

    public $price_items;

    public function getG_sid(){
        return $this->course_id;
    }

    public function getG_stype(){
        return $this->module;
    }

    public static function tableName(){
        return "{{%course}}";
    }

    public function rules(){
        return [
            [
                [
                    'course_title',
                    'course_created_at',
                    'course_updated_at',
                    'price_items',
                ], 'safe'
            ]
        ];
    }
}