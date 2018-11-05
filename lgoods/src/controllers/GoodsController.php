<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;

use common\models\RefundModel;
use lgoods\models\order\OrderModel;
use lgoods\models\refund\RfModel;
use lgoods\models\trans\Trans;
use lgoods\models\trans\TransModel;
use Yii;
use lgoods\models\goods\GoodsModel;
use lbase\Controller;
use yii\base\Event;
use lgoods\models\goods\GoodsEvent;
use yii\data\ActiveDataProvider;


class GoodsController extends Controller{

    public function actionHandle($type){
        $t = Yii::$app->db->beginTransaction();
        $notifyData = Yii::$app->request->getBodyParams();
        $notifyData ='<xml><appid><![CDATA[wxb8e63b3b3196d6a7]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[4]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[N]]></is_subscribe>
<mch_id><![CDATA[1489031722]]></mch_id>
<nonce_str><![CDATA[5qogjzc9roz4thcffib2soxqsx2nkbzm]]></nonce_str>
<openid><![CDATA[o82Odw-jLdQsZ1InClRz_3glyR30]]></openid>
<out_trade_no><![CDATA[TR112018535719105716]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[270736D96C21519427509AABE72E9952]]></sign>
<time_end><![CDATA[20181019115642]]></time_end>
<total_fee>4</total_fee>
<trade_type><![CDATA[NATIVE]]></trade_type>
<transaction_id><![CDATA[4200000169201810191219217090]]></transaction_id>
</xml>';
        $payment = TransModel::getPayment($type);
        try {
            $transData = $payment->handleNotify($notifyData, []);
            if($transData['code'] > 0){
                $payment->sayFail([]);
                exit();
            }
            $payOrder = TransModel::findPayTrace()->andWhere(['pt_belong_trans_number' => $transData['trans_number']])->one();
            $transModel = new TransModel();
            if(!$payOrder || !$transModel::updatePayOrderPayed($payOrder, ['notification' => $notifyData])){
                $payment->sayFail([]);
                exit();
            }
            TransModel::triggerPayed($payOrder);
            $payment->saySucc([]);
            $t->commit();
            exit();
        } catch (\Exception $e) {
            Yii::error($e);
            $t->rollBack();
            $payment->sayFail([]);
            exit();
        }
        
    }


    public function actionList(){
        $query = GoodsModel::findFull();
        $provider = new ActiveDataProvider([
            'query' => $query->asArray(),
        ]);
        return $this->succItems($provider->getModels(), $provider->totalCount);
    }

    public function actionView($index){
        $goodsData = GoodsModel::findFull()
                    ->andWhere(['=', 'g_id', $index])
                    ->asArray()
                    ->all();
        if(!$goodsData){
            return $this->notfound();
        }
        return $this->succ($goodsData);
    }

    public function actionCreate(){
        $t = $this->beginTransaction();
        try{
            $postData = Yii::$app->request->getBodyParams();
            $model = new GoodsModel();
            $goods = $model->createGoods($postData);
            if(!$goods){
                return $this->error(1, $model->getErrors());
            }
            $t->commit();
            return $this->succ($goods->toArray());
        }catch(\Exception $e){
            $t->rollback();
            throw $e;
        }
    }




}