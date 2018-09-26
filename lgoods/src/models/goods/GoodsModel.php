<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:11
 */
namespace lgoods\models\goods;

use Yii;
use yii\base\Object;
use lgoods\models\goods\GoodsEvent;
use yii\base\Event;
use lgoods\models\goods\Goods;
use lgoods\models\goods\GoodsSku;

class GoodsModel extends Object{

    CONST EVENT_GOODS_CREATE = 'goods_create';

    public static function getSkuFromGIndex(GoodsInterface $target, $params){
        $query = static::findSku();
        $query->andWhere(["=", 'g_sid', $target->getG_sid()]);
        $query->andWhere(['=', 'g_stype', $target->getG_stype()]);
        $query->andWhere(['=', 'sku_index', static::buildSkusIndexByParams($params)]);
        return $query->asArray()->one();
    }

    public static function getSkusFromGoods(GoodsInterface $target){
        $query = static::findSku();
        $query->andWhere(["=", 'g_sid', $target->getG_sid()]);
        $query->andWhere(['=', 'g_stype', $target->getG_stype()]);
        return $query->asArray()->all();
    }

    public static function findSku(){
        $gTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $select = [
            "$gTable.g_name",
            "$gTable.g_sid",
            "$gTable.g_stype",
            "$skuTable.*"
        ];
        $query = GoodsSku::find()
                    ->leftJoin($gTable, "g_id = sku_g_id")
                    ;
        $query->select($select);
        return $query;
    }

    public static function findValidSku(){
        $query = static::findSku();
        return $query;
    }

    public static function caculatePrice($sku, $buyParams = []){
        $priceItems = [
            'has_error' => 0,
            'error_des' => '',
            'og_total_num' => 0,
            'og_single_price' => 0,
            'og_total_price' => 0,
            'discount_items' => []
        ];
        $defualtBuyParams = [
            'buy_num' => 0, // 购买数量
            'customer_uid' => 0, // 购买用户id
            'discount_items' => [], // 用户使用折扣情况
        ];
        $buyParams = array_merge($defualtBuyParams, $buyParams);

        $priceItems['og_single_price'] = $sku['sku_price'];
        $priceItems['og_total_num'] = $buyParams['buy_num'];
        $priceItems['og_total_price'] = $priceItems['og_single_price']
                                        *
                                        $priceItems['og_total_num'];


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
        Event::trigger("\lgoods\models\goods\GoodsModel", static::EVENT_GOODS_CREATE, $event);
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