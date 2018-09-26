<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;

use lgoods\models\order\OrderModel;
use lgoods\models\trans\TransModel;
use Yii;
use lgoods\models\goods\GoodsModel;
use yii\web\Controller;
use yii\base\Event;
use lgoods\models\goods\GoodsEvent;



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
        $sku = GoodsModel::getSkuFromGIndex($course, ['version' => 1, 'ext_serv' => 0]);
        $skus = GoodsModel::getSkusFromGoods($course);
        $orderModel = new OrderModel();
        $orderData = [
            [
                'og_sku_id' => $skus[0]['sku_id'],
                'og_total_num' => 1,
                'discount_params' => [],
            ],
            [
                'og_sku_id' => $skus[1]['sku_id'],
                'og_total_num' => 2,
                'discount_params' => []
            ]
        ];


        $order = $orderModel->createOrderFromSkus($orderData);
        if(!$order){
            throw new \Exception(implode(',', $orderModel->getFirstErrors()));
        }

        $transModel = new TransModel();
        $trans = $transModel->createTransFromOrder($order, [
            'trs_timeout' => 3600,
            'trs_content' => ''
        ]);
        if(!$trans){
            throw new \Exception(implode(',', $transModel->getFirstErrors()));
        }

        $params = [
            'pt_pay_type' => 'alipay',
            'pt_pre_order_type' => 'data',

        ];
        $payOrder = $transModel->createPayOrderFromTrans($trans, $params);
        if(!$payOrder){
            throw new \Exception(implode(',', $transModel->getFirstErrors()));
        }


    }



    public function actionList(){

    }
}