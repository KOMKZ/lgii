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
use lgoods\models\refund\RfModel;
use lgoods\models\trans\TransModel;
use Yii;
use lbase\Controller;




class LrefundController extends Controller{
    public function actionCreate(){
        $t = $this->beginTransaction();
        try{
            $postData = Yii::$app->request->getBodyParams();
            $rfModel = new RfModel();
            $rf = $rfModel->createRefund($postData);
            if(!$rf){
                return $this->error(1, $rfModel->getErrors());
            }
            $t->commit();
            return $this->succ($rf->toArray());
        }catch(\Exception $e){
            throw $e;
            $t->rollback();
        }
    }

    public function actionUpdateStatus($index, $sub_index){
        $t = $this->beginTransaction();
        try{
            $rf = RfModel::find()->andWhere(['=', 'rf_num', $index])->one();
            if(!$rf){
                return $this->notfound();
            }
            switch ($sub_index){
                case "agree":
                    $res = $this->sendRefund($rf);
                    break;
                default:
                    throw new \Exception(sprintf("不支持的修改参数%s", $sub_index));
            }
            $t->commit();
            return $this->succ($res['rf']);
        }catch(\Exception $e){
            throw $e;
            $t->rollback();
        }
    }

    public function sendRefund($rf){
        $postData = Yii::$app->request->getBodyParams();
        $rfModel = new RfModel();
        $transModel = new TransModel();
        $rf = $rfModel->agreeRefund($rf);
        if(!$rf){
            return $this->error(1, $rfModel->getErrors());
        }

        $trans = $transModel->createTransFromRefund($rf);
        if(!$trans){
            return $this->error(1, $transModel->getErrors());
        }


        $payOrder = $transModel->createRfOrderFromTrans($trans, [
            'rf_order_trs_num' => $rf['rf_order_trs_num'],
            'rf_order_total_fee' => $rf['rf_fee'],
        ]);
        if(!$payOrder){
            return $this->error(1, $transModel->getErrors());
        }
        return [
            'rf' => $rf,
            'trans' => $trans,
            'pay_order' => $payOrder
        ];
    }

}