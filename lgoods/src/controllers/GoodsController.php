<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:13
 */
namespace lgoods\controllers;

use common\models\RefundModel;
use lgoods\models\goods\Goods;
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

    /**
     * @api get,/goods,Goods,查询商品接口
     *
     * @return #global_res
     * - data object#goods_items_list,返回课程信息
     */
    public function actionList(){
        $getData = Yii::$app->request->get();
        $query = GoodsModel::findFullForList();
        $provider = new ActiveDataProvider([
            'query' => $query->asArray(),
        ]);
        $items = GoodsModel::formatGoods($provider->getModels(), $getData);
        return $this->succItems($items, $provider->totalCount);
    }

    public function actionViewSku($index, $sub_index){
        $skuIndex = GoodsModel::ensureGoodsSkuIndexRight($sub_index);
        if(!$skuIndex){
            return $this->error(1, "参数错误");
        }
        $sku = GoodsModel::findSku()
                            ->andWhere(['=', 'sku_g_id', $index])
                            ->andWhere(['=', 'sku_index', $skuIndex])
                            ->asArray()
                            ->one();
        if(!$sku) {
            return $this->notfound();
        }
        return $this->succ($sku);
    }

    public function actionView($index){
        $getData = Yii::$app->request->get();
        $goodsData = GoodsModel::findFull()
                    ->andWhere(['=', 'g.g_id', $index])
                    ->asArray()
                    ->one();
        if(!$goodsData){
            return $this->notfound();
        }
        $goodsData = GoodsModel::formatOneGoods($goodsData, $getData);
        return $this->succ($goodsData);
    }

    public function actionListAttrs($index){
        $getData = Yii::$app->request->get();
        $attrs = GoodsModel::getGoodsListAttrs([$index], $getData);
        $data = [];
        if(isset($attrs[$index])){
            foreach($attrs[$index] as $attr){
                $data[] = $attr;
            }
        }
        return $this->succItems($data, count($data));
    }
    public function actionUpdate($index){
        $t = $this->beginTransaction();
        try{
            $goods = Goods::find()->where(['g_id' => $index])->one();
            if(!$goods){
                return $this->notfound();
            }

            $postData = Yii::$app->request->getBodyParams();
            $model = new GoodsModel();
            $goods = $model->updateGoods($goods, $postData);
            if(!$goods){
                return $this->error(1, $model->getErrors());
            }
            GoodsModel::ensureGoodsSkusRight($goods);
            $goodsFullData = GoodsModel::formatOneGoods($goods->toArray(), [
                'goods_attr_level' => 'all'
            ]);
            $t->commit();
            return $this->succ($goodsFullData);
        }catch(\Exception $e){
            $t->rollback();
            throw $e;
        }
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
            return $this->succ(GoodsModel::formatOneGoods($goods->toArray(), [
                'goods_attr_level' => 'all'
            ]));
        }catch(\Exception $e){
            $t->rollback();
            throw $e;
        }
    }
}
/**
 * @def #goods_items_list
 * - total_count integer,总数
 * - items array#goods_item,商品列表
 *
 * @def #goods_item
 * - g_id integer,商品id
 * 
 */