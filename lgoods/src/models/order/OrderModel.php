<?php
namespace lgoods\models\order;

use lbase\staticdata\ConstMap;
use lfile\models\FileModel;
use lgoods\helpers\PriceHelper;
use lgoods\models\sale\SaleModel;
use lgoods\models\trans\Trans;
use Yii;
use lgoods\models\goods\GoodsModel;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use lgoods\models\order\AfterPayedEvent;

class OrderModel extends Model{
    public static function getLevelFields($level){
        $map = [
            'all' => [
                'og_discount_info' => null,
                'od_discount_items' => null,
            ],
            'list' => [

            ]
        ];
        return $map[$level];
    }

    public static function formatOrders($orders, $params = []){
        foreach($orders as &$order){
            $order = static::formatOneOrder($order, $params);
        }
        return $orders;
    }
    public static function formatOneOrder($data, $params = []){
        $fields = static::getLevelFields(ArrayHelper::getValue($params, 'fields_level', 'all'));

        if(!empty($data['od_discount_items'])){
            $data['od_discount_items'] = json_decode($data['od_discount_items'], true);
        }else{
            $data['od_discount_items'] = [];
        }
        if(!empty($data['od_discount_des'])){
            $data['od_discount_des'] = json_decode($data['od_discount_des'], true);
        }else{
            $data['od_discount_des'] = [];
        }
        $data['od_price_str'] = PriceHelper::format($data['od_price']);
        $data['od_discount_str'] = PriceHelper::format($data['od_discount']);
        if(!empty($data['order_goods_list'])){
            $fModel = new FileModel();
            foreach($data['order_goods_list'] as &$ogItem){
                if(isset($ogItem['g_m_img_id'])){
                    $fModel = new FileModel();
                    $ogItem['g_m_img_url'] = $fModel->buildFileUrlStatic(FileModel::parseQueryId($ogItem['g_m_img_id']));
                }else{
                    $ogItem['g_m_img_url'] = '';
                }
                if(isset($ogItem['og_discount_items'])){
                    $ogItem['og_discount_items'] = json_decode($ogItem['og_discount_items'], true);
                }else{
                    $ogItem['og_discount_items'] = [];
                }
                if(isset($ogItem['og_discount_des'])){
                    $ogItem['og_discount_des'] = json_decode($ogItem['og_discount_des'], true);
                }else{
                    $ogItem['og_discount_des'] = [];
                }
            }
        }
        return $data;
    }

    public function ensureOrderCanPay($order){
        // todo
        return true;
    }


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

    public static function findOrderFull($params = []){
        $fields = static::getLevelFields(ArrayHelper::getValue($params, 'fields_level', 'all'));
        $oTable = Order::tableName();

        $query = Order::find()
                      ->with([
                          'order_goods_list' => function(Query $query) use($fields){
                              $joinDiscount = array_key_exists("og_discount_info", $fields);
                              $select = [
                                 "og_od_id",
                                 "og_g_id",
                                 "og_name",
                                  "og_total_num",
                                  "og_single_price",
                                  "og_total_price",
                                  "og_g_sid",
                                  "og_g_stype",
                                  "og_sku_id",
                                  "og_sku_index",
                                  "og_id",
                                 "oe.g_m_img_id",
                             ];
                             if($joinDiscount){
                                 $select[] = "og_discount_items";
                                 $select[] = "og_discount_des";
                             }
                             $query->select($select);
                          }
                      ]);
        $query->from([
            'o' => $oTable,
        ]);
        $tTable = Trans::tableName();
        $odTable = OrderDiscount::tableName();
        $select = [
            "o.od_id",
            "o.od_num",
            "o.od_pay_status",
            "o.od_created_at",
            "o.od_price",
            'o.od_discount',
            "t.trs_id",
            "o.od_pay_type",
            "t.trs_pay_num",
            "t.trs_num",
            "t.trs_target_id",
            "t.trs_type",
            "od.od_discount_des"
        ];
        $query->leftJoin(['t' => $tTable], "t.trs_type = :p1 and t.trs_target_id = o.od_id", [":p1" => Trans::TRADE_ORDER]);
        $query->leftJoin(['od' => $odTable], "od.od_id = o.od_id");
        if(array_key_exists('od_discount_items', $fields)){
            $select[] = "od.od_discount_items";
        }
        $query->select($select);
        return $query;
    }

    public function createOrderFromSkus($orderData){
        $orderData = ArrayHelper::index($orderData['order_goods_list'], 'og_sku_id');
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
        $totalDiscount = 0;
        $skuIds = [];
        $gids = [];
        foreach($skus as $index => $sku){
            $skuIds[] = $sku['sku_id'];
            $gids[] = $sku['sku_g_id'];
        }
        $discountItems = SaleModel::fetchGoodsRules([
            'g_id' => $skuIds,
            'sku_id' => $gids
        ]);
        $ogListData = [];
        foreach($skus as $index => $sku){
            $buyParams = [
                'buy_num' => $orderData[$sku['sku_id']]['og_total_num']
            ];
            $buyParams['discount_items'] = $discountItems;
            $priceItems = GoodsModel::caculatePrice($sku, $buyParams);
            if($priceItems['has_error']){
                throw new \Exception($priceItems['error_des']);
            }
            $totalPrice += $priceItems['og_total_price'];
            $totalDiscount += $priceItems['og_total_discount'];

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
                'og_discount_items' => json_encode($priceItems['discount_items']),
                'og_discount_des' => json_encode($priceItems['discount_items_des']),
                'og_created_at' => time(),
                'og_updated_at' => time(),
            ];
            ksort($ogData);
            $ogListData[] = $ogData;
        }
        $orderSaleRules = SaleModel::fetchOrderRules([
            'total_price' => $totalPrice
        ]);
        $buyParams = [
            'discount_items' => $orderSaleRules
        ];
        $priceItems = static::caculatePrice([
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
        ], $buyParams);
        if($priceItems['has_error']){
            throw new \Exception($priceItems['error_des']);
        }

        $order = new Order();
        $order->od_pid = 0;
        $order->od_belong_uid = 0;
        $order->od_price = $priceItems['total_price'];
        $order->od_discount = $priceItems['total_discount'];
        $order->od_pay_status = Order::PS_NOT_PAY;
        $order->od_paid_at = 0;
        $order->od_title = static::buildOdTitleFromGoods($ogListData);
        $order->od_num = static::buildOrderNumber();
        $order->insert(false);
        foreach($ogListData as $i => $ogData){
            $ogListData[$i]['og_od_id'] = $order->od_id;
        }
        static::batchInsertOgData($ogListData);
        static::batchInsertODiscountData([
            [
                'od_id' => $order->od_id,
                'od_discount_items' => json_encode($priceItems['discount_items']),
                'od_discount_des' => json_encode($priceItems['discount_items_des'])
            ]
        ]);
        return $order;

    }

    public static function checkOrderFromOgList($ogList, $buyParams = []){
        $skuIds = [];
        $gids = [];
        foreach($ogList as $item){
            $skuIds[] = $item['ci_sku_id'];
            $gids = $item['ci_g_id'];
        }
        $discountItems = SaleModel::fetchGoodsRules([
            'g_id' => $skuIds,
            'sku_id' => $gids
        ]);
        $totalPrice = 0;
        $totalDiscount = 0;
        foreach($ogList as $item){
            $buyParams = [
                'buy_num' => $item['ci_amount'],
            ];
            $buyParams['discount_items'] = $discountItems;
            $priceItems = GoodsModel::caculatePrice($item, $buyParams);
            if($priceItems['has_error']){
                throw new \Exception($priceItems['error_des']);
            }
            $totalPrice += $priceItems['og_total_price'];
            $totalDiscount += $priceItems['og_total_discount'];
        }
        $orderSaleRules = SaleModel::fetchOrderRules([
            'total_price' => $totalPrice
        ]);
        $buyParams = [
            'discount_items' => $orderSaleRules
        ];
        $priceItems = static::caculatePrice([
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
        ], $buyParams);
        if($priceItems['has_error']){
            throw new \Exception($priceItems['error_des']);
        }
        return $priceItems;
    }

    public static function caculatePrice($order, $buyParams = []){
        $priceItems = [
            'has_error' => 0,
            'error_des' => '',
            'total_price' => 0,
            'total_discount' => 0,
            'discount_items' => [],
            'discount_items_des' => []
        ];
        $defualtBuyParams = [
            'discount_items' => [], // 用户使用折扣情况
        ];
        $buyParams = array_merge($defualtBuyParams, $buyParams);
        $priceItems['total_price'] = $order['total_price'];
        $priceItems['total_discount'] = $order['total_discount'];
        foreach($buyParams['discount_items'] as $saleRule){
            if(!SaleModel::checkAllow($order, $saleRule)){continue;}
            $discount = $saleRule->discount($priceItems);
            $discountParams = array_merge($saleRule->toArray(), [
                'discount' => $discount,
                'total_price' => $priceItems['total_price'],
                'total_discount' => $priceItems['total_price'],
            ]);
            $priceItems['discount_items'][] = $discountParams;
            $priceItems['discount_items_des'][] = static::buildDiscountItemDes($discountParams);
            $priceItems['total_price'] -= $discount;
            $priceItems['total_discount'] += $discount;
        }
        return $priceItems;
    }
    public static function buildDiscountItemDes($data){
        $ruleNameMap =  ConstMap::getConst('sr_object_type');
        return sprintf("订单原价%s,折扣为%s,优惠后价格为%s(使用%s规则-优惠%s)",
            PriceHelper::format($data['total_price']),
            PriceHelper::format($data['discount']),
            PriceHelper::format($data['total_price'] - $data['discount'])
            ,$ruleNameMap[$data['sr_object_type']]
            ,$data['sr_name']
        );
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
    public static function batchInsertODiscountData($itemsData){
        return Yii::$app->db->createCommand()->batchInsert(OrderDiscount::tableName(), [
            'od_id',
            'od_discount_items',
            'od_discount_des',
        ], $itemsData)->execute();
    }
    public static function batchInsertOgData($ogListData){
        return Yii::$app->db->createCommand()->batchInsert(OrderGoods::tableName(), [
            'og_created_at',
            'og_discount_des',
            'og_discount_items',
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