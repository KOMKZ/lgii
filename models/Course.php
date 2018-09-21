<?php
namespace app\models;

use yii\base\Model;
use lgoods\models\GoodsInterface;
class Course extends Model implements GoodsInterface{
    public $course_title;
    public $course_id;
    public $module;
    public $course_created_at;
    public $course_updated_at;
    public $price_items;

    public function rules(){
        return [
            [
                [
                    'course_title',
                    'course_id',
                    'module',
                    'course_created_at',
                    'course_updated_at',
                    'price_items',
                ], 'safe'
            ]
        ];
    }
}