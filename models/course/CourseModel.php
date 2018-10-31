<?php
namespace app\models\course;

use yii\base\Model;

class CourseModel extends Model{
    public function createCourse($data){
        $course = new Course();
        if(!$course->load($data, '') || !$course->validate()){
            $this->addErrors($course->getErrors());
            return false;
        }
        $course->module = 'cour';
        $course->insert(false);
        return $course;
    }
    public static function find(){
        return Course::find();
    }
}