<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:11
 */
namespace lgoods\models;

use Yii;
use yii\base\Object;
use lgoods\models\GoodsEvent;
use yii\base\Event;
use lgoods\models\Goods;
use lgoods\models\GoodsSku;

class GoodsModel extends Object{

    CONST EVENT_GOODS_CREATE = 'goods_create';

    public static function getSkuFromIndex($index){

    }
    public static function caculatePrice(GoodsInterface $goods, $skuParams = [], $buyParams = []){
        $priceItems = [
            'has_error' => 0,
            'price_paid' => 0,
            'discount' => 0,
            'discount_items' => []
        ];
        $buyParams = [
            'buy_num' => 0, // 购买数量
            'customer_uid' => 0, // 购买用户id
            'discount_items' => [], // 用户使用折扣情况
        ];
        return $priceItems;
    }

    public static function handleGoodCreate($event){
        $goodsData = $event->goodsData;
        $target = $event->object;
        $goods = new Goods();
        if(!$goods->load($goodsData, '') || !$goods->validate()){
            throw new \Exception(implode(",", $goods->getFirstErrors()));
        }
        $goods->insert(false);
        // 创建sku
        if(!empty($goodsData['price_items'])){
            $skuData = [];
            foreach ($goodsData['price_items'] as $key => $skuParams){
                if(!isset($skuParams['price']) || !is_numeric($skuParams['price'])){
                    throw new \Exception(sprintf("%s %s price非法", $key, implode(',', $skuParams)));
                }
                $skuPrice = $skuParams['price'];
                unset($skuParams['price']);
                $skuIndex = static::buildSkusIndexByParams($skuParams);
                $skuData[] = [
                    'sku_g_id' => $goods->g_id,
                    'sku_index' => $skuIndex,
                    'sku_name' => '',
                    'sku_price' => $skuPrice,
                ];
            }
            $skus = static::createGoodsSkus($skuData);
            if(!$skus){
                throw new \Exception("创建sku失败");
            }
        }
    }

    public static function createGoodsSkus($skuListData){
        $t = Yii::$app->db->beginTransaction();
        try{
            $skus = [];
            foreach($skuListData as $skuData){
                $sku = new GoodsSku();
                if(!$sku->load($skuData, '') || !$sku->validate()){
                    throw new \Exception(implode(',', $sku->getFirstErrors()));
                }
                $sku->insert(false);
                $skus[] = $sku;
            }
            $t->commit();
            return $skus;
        }catch(\Exception $e){
            Yii::error($e);
            $t->rollBack();
            return false;
        }
    }

    public static function triggerGoodsCreate($object, $goodsData){
        $event = new GoodsEvent();
        $event->goodsData = $goodsData;
        Event::trigger("\lgoods\models\GoodsModel", static::EVENT_GOODS_CREATE, $event);
    }

    public static function buildSkusIndexByParams($params){
        ksort($params);
        $str = [];
        foreach($params as $name => $value){
            $str[] = sprintf("%s:%s", $name, $value);
        }
        return implode('-', $str);
    }

}