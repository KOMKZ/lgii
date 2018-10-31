<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace app\controllers;


use app\models\course\Course;
use app\models\course\CourseModel;
use lbase\Controller;
use Yii;
use lgoods\models\goods\GoodsModel;
use yii\data\ActiveDataProvider;


class CourseController extends Controller{


    public function actionCreate(){
        $data = Yii::$app->request->getBodyParams();
        $model = new CourseModel();
        $course = $model->createCourse($data);
        if(!$course){
            return $this->error(1, $model->getErrors());
        }
        GoodsModel::triggerGoodsCreate($course, [
            'g_name' => $course['course_title'],
            'g_sid' => $course['course_id'],
            'g_stype' => $course['module'],
            'price_items' => $course['price_items'],
        ]);
        return $this->succ($course->toArray());
    }

    public function actionList(){
        $query = CourseModel::find();
        $provider = new ActiveDataProvider([
            'query' => $query->asArray(),
        ]);
        return $this->succItems($provider->getModels(), $provider->totalCount);
    }

}