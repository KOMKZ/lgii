<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;

use lgoods\models\goods\GoodsModel;
use lgoods\models\order\OrderModel;
use lgoods\models\trans\TransModel;
use Yii;
use lbase\Controller;




class LorderController extends Controller{

    public function actionView($index){
        $order = OrderModel::findOrderFull()->andWhere(['=', 'od_num', $index])->asArray()->one();
        if(!$order){
            return $this->notfound();
        }
        return $this->succ($order);
    }

    public function actionCreate(){
        $t = $this->beginTransaction();
        try{
            $postData = Yii::$app->request->getBodyParams();
            $orderModel = new OrderModel();
            $order = $orderModel->createOrderFromSkus($postData);
            if(!$order){
                return $this->error(1, $orderModel->getErrors());
            }
            $orderData = OrderModel::findOrderFull()->andWhere(['=', 'od_id', $order['od_id']])->asArray()->one();
            $t->commit();
            return $this->succ($orderData);
        }catch(\Exception $e){
            throw $e;
            $t->rollback();
        }
    }

    public function actionCreateTrans($index){
        $t = $this->beginTransaction();
        try{
            $order = OrderModel::findOrder()->andWhere(['=', 'od_num', $index])->one();
            if(!$order){
                return $this->notfound();
            }
            $postData = Yii::$app->request->getBodyParams();
            $transModel = new TransModel();
            $trans = $transModel->createTransFromOrder($order, [
                'trs_timeout' => 500,
                'trs_content' => ''
            ]);
            if(!$trans){
                return $this->error(1, $transModel->getErrors());
            }
            $t->commit();
            return $this->succ($trans->toArray());
        }catch(\Exception $e){
            throw $e;
            $t->rollback();
        }
    }



}