<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-9-9
 * Time: 下午11:11
 */
namespace lgoods\models\goods;

use lgoods\models\attr\Attr;
use lgoods\models\attr\AttrModel;
use lgoods\models\attr\Option;
use Yii;
use yii\base\Model;
use yii\base\Object;
use lgoods\models\goods\GoodsEvent;
use yii\base\Event;
use lgoods\models\goods\Goods;
use lgoods\models\goods\GoodsSku;
use yii\helpers\ArrayHelper;

class GoodsModel extends Model{

    CONST EVENT_GOODS_CREATE = 'goods_create';
    public static function getDefaultGoodsFieldParams(){
        return [
            'g_attr_level' => 'all'
        ];
    }
    public static function formatOneGoods($data, $params = []){
        $params = array_merge(static::getDefaultGoodsFieldParams(), $params);
        if(!empty($params['g_attr_level'])){
            $attrs = empty($params['attrs']) ? static::getGoodsListAttrs([$data['g_id']], $params) : $params['attrs'];
            $data['g_attrs'] = isset($attrs[$data['g_id']]) ? $attrs[$data['g_id']] : [];
        }else{
            $data['g_attrs'] = [];
        }
        return $data;
    }
    public static function formatGoods($dataList, $params = []){
        if(!empty($params['g_attr_level'])){
            $gids = [];
            foreach($dataList as $item){
                $gids[] = $item['g_id'];
            }
            $params['attrs'] = static::getGoodsListAttrs($gids, $params);
        }
        foreach($dataList as $key => &$data){
            $data = static::formatOneGoods($data, $params);
        }
        return $dataList;
    }
    public static function getGoodsAttrs($gid, $params){
        $attrs = static::getGoodsListAttrs([$gid], $params);
        return isset($attrs[$gid]) ? $attrs[$gid] : [];
    }
    public static function getGoodsListAttrs($gids, $params){
        $level = ArrayHelper::getValue($params, 'g_attr_level', '');
        $levelMap = [
            'long' => Attr::A_TYPE_FULL_TEXT,
            'sku' => Attr::A_TYPE_SKU,
            'short' => Attr::A_TYPE_NORMAL,
        ];

        $aTable = Attr::tableName();
        $optTable = Option::tableName();
        $query = Option::find()
            ->select([
                "{$aTable}.a_id",
                "{$aTable}.a_name",
                "{$optTable}.opt_id",
                "{$optTable}.opt_name",
                "{$optTable}.opt_value",
                "{$optTable}.opt_object_id"
            ])
            ->leftJoin($aTable, "{$aTable}.a_id = {$optTable}.opt_attr_id")
            ->andWhere(['in', 'opt_object_id', $gids])
            ->andWhere(['=', 'opt_object_type', Option::OBJECT_TYPE_GOODS ])
            ->asArray()
            ;
        if(($level != 'all') && isset($levelMap[$level])){
            $query->andWhere(['=', 'a_type', $levelMap[$level]]);
        }
        $result = $query->all();
        $attrList = [];
        $map = [];
        foreach($result as $item){
            $gid = $item['opt_object_id'];
            $aid = $item['a_id'];
            $num = 0;
            if(!isset($attrList[$gid])) {
                $attrList[$gid] = [];
                $map[$gid] = [];
            }
            if(!isset($map[$gid][$aid])){
                $num = count($map[$gid]);
                $attrList[$gid][$num] = [
                    'values' => [],
                    'a_name' => $item['a_name'],
                    'a_id' => $aid,
                ];
                $map[$gid][$aid] = $num;
            }
            $index = $map[$gid][$aid];

            $attrList[$gid][$index]['values'][] = [
                'opt_id' => $item['opt_id'],
                'opt_name' => $item['opt_name'],
                'opt_value' => $item['opt_value']
            ];
        }
        return $attrList;

    }





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

    public static function findWithSkus(){
        $query = Goods::find()
                    ->with("goods_skus");
        return $query;
    }

    public static function findFull(){
        $query = Goods::find()
            ->with("goods_skus");
        return $query;
    }

    public static function findValidSku(){
        $query = static::findSku();
        return $query;
    }

    public static function findGoodsWithMSku(){
        $skuTable = GoodsSku::tableName();
        $gTable = Goods::tableName();
        $select = [
            "{$gTable}.*",
            "{$skuTable}.sku_price",
            "{$skuTable}.sku_id",
            "{$skuTable}.sku_index",
            "{$skuTable}.sku_is_master",
        ];
        $query = Goods::find()
                    ->leftJoin($skuTable, "sku_g_id = g_id and sku_is_master = 1")
            ;
        $query->select($select);
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
        $model = new static();
        $goods = $model->createGoods($goodsData);
        if(!$goods){
            throw new \Exception(implode(',', $model->getFirstErrors()));
        }
    }
    public function deleteGoodsOptions($condition){
        return Option::deleteAll([
            'opt_object_id' => $condition['g_id'],
            'opt_object_type' => Option::OBJECT_TYPE_GOODS,
            'opt_id' => $condition['opt_ids']
        ]);
    }
    public function updateGoodsOptions($goods, $optionsData){
        $ids = ArrayHelper::getColumn($optionsData, 'opt_id');
        $options = AttrModel::findOption()->andWhere(['in', 'opt_id', $ids])->indexBy('opt_id')->all();
        $count = 0;
        foreach($optionsData as $key => $data){
            if(!isset($options[$data['opt_id']])){
                $this->addError('g_attrs', sprintf("%s:%s", $key, '更新的属性值不存在'));
                return false;
            }
            $option = $options[$data['opt_id']];
            $option->scenario = 'update';
            if(!$option->load($data, '') || !$option->validate()){
                $this->addError('g_attrs', sprintf("%s:%s", $key, implode(',', $option->getFirstErrors())));
                return false;
            }
            $option->update(false);
            $count++;
        }
        return $count;
    }

    public function createGoodsOptions($goods, $options){
        $option = new Option();
        $insertData = [];
        foreach($options as $key => $optionData){
            if(!$option->load($optionData, '') || !$option->validate()){
                $this->addError("", $key . ":" . implode(',', $option->getFirstErrors()));
                return false;
            }
            $insertData[] = [
                'opt_name' => $option->opt_name,
                'opt_value' => $option->opt_value,
                'opt_attr_id' => $option->opt_attr_id,
                'opt_object_id' => $goods->g_id,
                'opt_object_type' => Option::OBJECT_TYPE_GOODS,
                'opt_created_at' => time(),
                'opt_updated_at' => time()
            ];
        }
        return Yii::$app->db->createCommand()
            ->batchInsert(Option::tableName(), [
                'opt_name',
                'opt_value',
                'opt_attr_id',
                'opt_object_id',
                'opt_object_type',
                'opt_created_at',
                'opt_updated_at',
            ], $insertData)->execute();
    }
    public function updateGoods($goods, $goodsData){
        if(!empty($goodsData['g_options'])){
            list($newOptions, $oldOptions) = static::fetchNewOldOptions($goodsData['g_options']);
            if($newOptions){
                $count = $this->createGoodsOptions($goods, $newOptions);
                if(false === $count){
                    return false;
                }
            }
            if($oldOptions){
                $count = $this->updateGoodsOptions($goods, $oldOptions);
                if(false === $count){
                    return false;
                }
            }
        }
        if(!empty($goodsData['g_del_options'])){
            $count = static::deleteGoodsOptions([
                'g_id' => $goods['g_id'],
                'opt_ids' => $goodsData['g_del_options'],
            ]);
        }
        return $goods;
    }

    public static function fetchNewOldOptions($options){
        $newOptions = [];
        $oldOptions = [];
        foreach($options as $option){
            if(isset($option['opt_id'])){
                $oldOptions[] = $option;
            }else{
                $newOptions[] = $option;
            }
        }
        return [$newOptions, $oldOptions];
    }

    public  function createGoods($goodsData){
        $goods = new Goods();
        if(!$goods->load($goodsData, '') || !$goods->validate()){
            $this->addErrors($goods->getErrors());
            return false;
        }
        $goods->insert(false);
        if(!empty($goodsData['g_options'])){
            $attrs = $this->createGoodsOptions($goods, $goodsData['g_options']);
            if(false === $attrs){
                return false;
            }
        }
        if(!empty($goodsData['ac_id'])){
            $attrModel = new AttrModel();
            $ocmap = $attrModel->createObjectCollectAssign([
                'ac_id' => $goodsData['ac_id']
                ,'ocm_object_id' => $goods->g_id
                ,'ocm_object_type' => Option::OBJECT_TYPE_GOODS
            ]);
            if(!$ocmap){
                $this->addErrors($attrModel->getErrors());
                return false;
            }

        }
        // 创建sku
        if(!empty($goodsData['price_items'])){
            $skuData = [];
            $hasMaster = 0;
            $attrs = static::getGoodsAttrs($goods->g_id, ['g_attr_level' => 'sku']);
            if(!$attrs){
                $this->addError('price_items', '当前商品还没有定义属性列表');
                return false;
            }
            foreach($attrs as $key => &$attr){
                $attr['values'] = ArrayHelper::index($attr['values'], 'opt_value');
            }
            $attrs = ArrayHelper::index($attrs, 'a_id');
            foreach ($goodsData['price_items'] as $key => $skuParams){
                if(!isset($skuParams['price']) || !is_numeric($skuParams['price'])){
                    throw new \Exception(sprintf("%s %s price非法", $key, implode(',', $skuParams)));
                }
                $isMaster = ArrayHelper::getValue($skuParams, 'is_master', 0);
                $hasMaster = $isMaster || $hasMaster;
                $skuPrice = $skuParams['price'];
                unset($skuParams['is_master']);
                unset($skuParams['price']);

                foreach($skuParams as $aid => $value){
                    $skuNameParams[$attrs[$aid]['a_name']] = $attrs[$aid]['values'][$value]['opt_name'];
                }
                $skuIndexName = static::buildSkusIndexByParams($skuNameParams);
                $skuIndex = static::buildSkusIndexByParams($skuParams);
                $skuData[] = [
                    'sku_g_id' => $goods->g_id,
                    'sku_index' => $skuIndex,
                    'sku_name' => $skuIndexName,
                    'sku_price' => $skuPrice,
                    'sku_is_master' => $isMaster,
                ];
            }
            if(!$hasMaster){
                throw new \Exception("价格参数必须指定指定主价格");
            }
            $skus = static::createGoodsSkus($skuData);
            if(!$skus){
                throw new \Exception("创建sku失败");
            }
        }
        return $goods;
    }
    public static function ensureGoodsSkusRight($data){
        $attrs = static::getGoodsAttrs($data['g_id'], ['g_attr_level' => 'sku']);
        if(!$attrs){
            return null;
        }
        $skuIndexs = static::buildSkuIndexFromAttrs($attrs);
        console($skuIndexs);
    }

    public static function buildSkuIndexFromAttrs($attrs, $name = ''){
        $attr = array_pop($attrs);
        if(!$attr){
            return ;
        }
        $values = [];
        foreach($attr['values'] as $option){
            $values[] = []
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