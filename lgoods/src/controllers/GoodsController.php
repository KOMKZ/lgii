<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;

use Yii;
use lgoods\models\GoodsModel;
use yii\web\Controller;
use yii\base\Event;
use lgoods\models\GoodsEvent;



class GoodsController extends Controller{


    public function actionCreate(){
        Yii::$app->db->beginTransaction();
        $courseData = [
            'course_title' => '安全防爆电器',
            'course_id' => 1,
            'module' => 'cour',
            'course_created_at' => time(),
            'course_updated_at' => time(),
            'price_items' => [
                ['version' => 1, 'ext_serv' => 0, 'price' => 10000],
                ['version' => 2, 'ext_serv' => 0, 'price' => 12000],
                ['version' => 1, 'ext_serv' => 1, 'price' => 11000],
                ['version' => 2, 'ext_serv' => 1, 'price' => 13000],
            ]
        ];
        $course = new \app\models\Course();
        $course->load($courseData, '');

        GoodsModel::triggerGoodsCreate($course, [
            'g_name' => $courseData['course_title'],
            'g_sid' => $courseData['course_id'],
            'g_stype' => $courseData['module'],
            'price_items' => $courseData['price_items'],
        ]);

        $course->getSkuFromIndex(['version' => 1, 'ext_serv' => 0]);
    }



    public function actionList(){
        $course = new \app\models\Course();
        $skuParams = [
            'version' => '1', // 套装1
            'ext_serv' => '1' // 选择额外服务
        ];
        $buyParams = [
            'buy_num' => 1, // 购买数量
            'customer_uid' => 1, // 购买用户id
            'discount_items' => [], // 用户使用折扣情况
        ];
        $priceItems = GoodsModel::caculatePrice($course);

        $pricePaid = $priceItems['price_paid'];
        $discount = $priceItems['discount'];
        $discountItems = $priceItems['discount_items'];
        console($priceItems);
    }
}