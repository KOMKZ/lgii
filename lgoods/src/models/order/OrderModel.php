<?php
namespace lgoods\models\order;

use lgoods\models\trans\Trans;
use Yii;
use lgoods\models\goods\GoodsModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use lgoods\models\order\AfterPayedEvent;

class OrderModel extends Model{

    public static function handleReceivePayedEvent($event){
        $trans = $event->sender;
        $payOrder = $event->payOrder;
        $order = static::findOrder()
                        ->andWhere(['=', 'od_id', $trans->trs_target_id])
                        ->one();
        ;
        if(Order::PS_PAID == $order['od_pay_status']){
            return true;
        }
        $order->od_pay_status = Order::PS_PAID;
        $order->od_paid_at = $trans->trs_pay_at;
        $order->od_pay_type = $trans->trs_pay_type;
        $order->od_pay_num = $trans->trs_pay_num;
        $order->od_trs_num = $trans->trs_num;
        if(false == $order->update(false)){
            throw new \Exception("订单修改失败");
        }
        $order->trigger(Order::EVENT_AFTER_PAID);
    }
    public static function ensureCanRefund($orderData){
        return [true, ''];
    }
    public static function findOrder(){
        return Order::find();
    }

    public static function findOrderFull(){
        $query = Order::find()
                      ->with("order_goods_list");
        $tTable = Trans::tableName();
        $oTable = Order::tableName();
        $select = ["{$oTable}.*"];
        $query->leftJoin($tTable, "$tTable.trs_type = :p1 and {$tTable}.trs_target_id = {$oTable}.od_id", [":p1" => Trans::TRADE_ORDER]);
        $select[] = "{$tTable}.*";
        $query->select($select);
        return $query;
    }

    public function createOrderFromSkus($orderData){
        $orderData = ArrayHelper::index($orderData, 'og_sku_id');
        $skuIds = array_keys($orderData);
        $skus = GoodsModel::findValidSku()
                          ->andWhere(['in', 'sku_id', $skuIds])
//                          ->indexBy('sku_id')
                          ->asArray()
                          ->all()
                          ;
        if(count($skus) != count($skuIds)){
            throw new \Exception("选定的商品存在遗漏");
        }
        $totalPrice = 0;
        $allDiscountItems = [];
        $ogListData = [];
        foreach($skus as $index => $sku){
            $buyParams = [
                'buy_num' => $orderData[$sku['sku_id']]['og_total_num']
            ];
            $priceItems = GoodsModel::caculatePrice($sku, $buyParams);
            if($priceItems['has_error']){
                throw new \Exception($priceItems['error_des']);
            }
            $totalPrice += $priceItems['og_total_price'];
            $allDiscountItems[$sku['sku_id']] = $priceItems['discount_items'];
            $ogData = [
                'og_total_num' => $priceItems['og_total_num'],
                'og_single_price' => $priceItems['og_single_price'],
                'og_total_price' => $priceItems['og_total_price'],
                'og_name' => $sku['g_name'],
                'og_g_id' => $sku['sku_g_id'],
                'og_g_sid' => $sku['g_sid'],
                'og_g_stype' => $sku['g_stype'],
                'og_sku_id' => $sku['sku_id'],
                'og_sku_index' => $sku['sku_index'],
                'og_created_at' => time(),
                'og_updated_at' => time(),
            ];
            ksort($ogData);
            $ogListData[] = $ogData;

        }
        $order = new Order();
        $order->od_pid = 0;
        $order->od_belong_uid = 0;
        $order->od_price = $totalPrice;
        $order->od_pay_status = Order::PS_NOT_PAY;
        $order->od_paid_at = 0;
        $order->od_title = static::buildOdTitleFromGoods($ogListData);
        $order->od_num = static::buildOrderNumber();
        $order->insert(false);
        foreach($ogListData as $i => $ogData){
            $ogListData[$i]['og_od_id'] = $order->od_id;
        }

        static::batchInsertOgData($ogListData);

        return $order;

    }

    public static function buildOrderNumber(){
        list($time, $millsecond) = explode('.', microtime(true));
        $string = sprintf("OD%s%04d", date("HYisdm", $time), $millsecond);
        return $string;
    }

    public static function buildOdTitleFromGoods($ogListData){
        return count($ogListData) > 1 ?
            sprintf("%s等%s件商品", $ogListData[0]['og_name'], count($ogListData))
            :
            sprintf("%s 1件商品", $ogListData[0]['og_name']);
    }

    public static function batchInsertOgData($ogListData){
        return Yii::$app->db->createCommand()->batchInsert(OrderGoods::tableName(), [
            'og_created_at',
            'og_g_id',
            'og_g_sid',
            'og_g_stype',
            'og_name',
            'og_single_price',
            'og_sku_id',
            'og_sku_index',
            'og_total_num',
            'og_total_price',
            'og_updated_at',
            'og_od_id',
        ], $ogListData)->execute();
    }
}