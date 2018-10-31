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




class LtransController extends Controller{
    public function actionCreatePayOrder($index){
        $t = $this->beginTransaction();
        try{
            $trans = TransModel::findTrans()->andWhere(['=', 'trs_num', $index])->one();
            if(!$trans){
                return $this->notfound();
            }
            $transModel = new TransModel();
            $postData = Yii::$app->request->getBodyParams();
            $payOrder = $transModel->createPayOrderFromTrans($trans, $postData);
            if(!$payOrder){
                return $this->error(1, $transModel->getErrors());
            }
            $t->commit();
            return $this->succ($payOrder->toArray());
        }catch(\Exception $e){
            throw $e;
            $t->rollback();
        }
    }
}