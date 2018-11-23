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
use yii\data\ActiveDataProvider;


class LorderController extends Controller{

    /**
     * @api get,/lorder,Order,获取订单详情
     * - id required,string,in_query,订单编号od_num，支持模糊查询
     *
     * @return #global_res
     * - data object#order_item_list,返回创建订单详情列表对象
     *
     */
    public function actionList(){
        $query = OrderModel::findOrderFull()->asArray();
        $provider = new ActiveDataProvider([
            'query' => $query
        ]);
        return $this->succItems($provider->getModels(), $provider->totalCount);
    }


    /**
     * @api get,/lorder/{id},Order,获取订单详情
     * - id required,string,in_path,订单编号od_num
     *
     * @return #global_res
     * - data object#order_item,返回创建订单详情对象
     *
     */
    public function actionView($index){
        $order = OrderModel::findOrderFull()->andWhere(['=', 'o.od_num', $index])->asArray()->one();
        if(!$order){
            return $this->notfound();
        }
        return $this->succ($order);
    }

    /**
     * @api post,/lorder,Order,创建一条订单
     * - order_goods_list required,array#og_item_param,in_body,订单购买商品列表
     *
     * @return #global_res
     * - data object#order_item,返回创建订单对象
     *
     */
    public function actionCreate(){
        $t = $this->beginTransaction();
        try{
            $postData = Yii::$app->request->getBodyParams();
            $orderModel = new OrderModel();
            $order = $orderModel->createOrderFromSkus($postData);
            if(!$order){
                return $this->error(1, $orderModel->getErrors());
            }
            $orderData = OrderModel::findOrderFull()->andWhere(['=', 'o.od_id', $order['od_id']])->asArray()->one();
            $t->commit();
            return $this->succ(OrderModel::formatOneOrder($orderData));
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
/**
 * @def #order_item_list
 * - total_count integer,总数量
 * - order_item_list array#order_item,订单对象列表
 *
 * @def #og_item_param
 * - og_sku_id required,integer,商品sku的id
 * - og_total_num required,integer,购买的数量
 * - discount_params optional,string,用户折扣选择
 *
 * @def #order_item
 * - od_id integer,订单id
 * - od_num string,订单编号
 * - od_created_at integer,下单时间
 * - od_price integer,订单价格
 * - od_discount integer,订单折扣
 * - trs_id integer,交易id
 * - od_discount_des array#string,订单折扣描述列表
 * - order_goods_list array#order_goods_item,订单明细列表
 * - od_price_str string,订单价格展示值
 * - od_discount_str string,订单折扣展示值
 *
 * @def #order_goods_item
 * - og_name string,明细名称，即商品名称
 * - g_m_img_url string,商品主图片url
 *
 */