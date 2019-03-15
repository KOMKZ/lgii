<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-7
 * Time: 下午5:52
 */
namespace lgoods\models\sale;

use lbase\helpers\ArrayHelper;
use yii\base\Model;

class SaleModel extends Model{
    public static function checkAllow($target, $rule){
        switch ($rule['sr_object_type']){
            case SaleEnum::SR_TYPE_GOODS:
                return $target['g_id'] == $rule['sr_object_id'];
                break;
            case SaleEnum::SR_TYPE_SKU:
                return $target['sku_id'] == $rule['sr_object_id'];
                break;
            case SaleEnum::SR_TYPE_CATEGORY:
                return $target['g_cls_id'] == $rule['sr_object_id'];
                break;
            case SaleEnum::SR_TYPE_ORDER:
                return !empty($target['od_id']);
                break;
        }
        return false;
    }
    public static function getGlobalRuleFilterParams($params = []){
        return array_merge([
            'exclude_defs' => [
                SaleEnum::SR_TYPE_SKU => [
                    SaleEnum::SR_TYPE_GOODS,
                    SaleEnum::SR_TYPE_CATEGORY
                ],
                SaleEnum::SR_TYPE_GOODS => [
                    SaleEnum::SR_TYPE_CATEGORY
                ]
            ],
            'is_order_filter' => true,
        ], $params);
    }
    public static function fetchTargetRules($range){
        $rules = [];
        $query = static::find();
        foreach($range as $rangeCond){
            $query->orWhere($rangeCond);
        }
        $query->andWhere(['=', 'sr_status', SaleEnum::SR_STATUS_VALID]);
        $query->andWhere(['<=', 'sr_start_at', time()]);
        $query->andWhere(['>=', 'sr_end_at', time()]);
        $rules = $query->all();
        return $rules;
    }
    public static function fetchOrderRules($data = []){
        $ruleRange = [
            [
                'sr_object_id' => 0,
                'sr_object_type' => SaleEnum::SR_TYPE_ORDER
            ]
        ];
        $saleRules = SaleModel::fetchTargetRules($ruleRange);
        $saleRules = SaleModel::filterRules($saleRules, static::getGlobalRuleFilterParams($data));
        return $saleRules;

    }
    public static function fetchGoodsRules($data, $group = false){
        if(isset($data['sku_id'])){
            $ruleRange[] = [
                'sr_object_id' => $data['sku_id'],
                'sr_object_type' => SaleEnum::SR_TYPE_SKU
            ];
        }
        if(isset($data['g_id'])){
            $ruleRange[] =                 [
                'sr_object_id' => $data['g_id'],
                'sr_object_type' => SaleEnum::SR_TYPE_GOODS
            ];
        }
        if($ruleRange){
            $saleRules = SaleModel::fetchTargetRules($ruleRange);
            $saleRules = SaleModel::filterRules($saleRules, static::getGlobalRuleFilterParams([
                'is_order_filter' => false
            ]));
//            if($group){
//                $result = [];
//                foreach($saleRules as $rule){
//                    console($rule->toArray());
//                }
//            }
        }else{
            $saleRules = [];
        }
        return $saleRules;
    }

    public static function filterRules($rules, $filterParams = []){
        $excludeTypes = [];
        $excludeIds = [];
        $maxPrice = 0;
        $maxPriceId = 0;
        foreach($rules as $rule){
            $type = $rule['sr_object_type'];

            if($filterParams['exclude_defs']){
                if(isset($filterParams['exclude_defs'][$type])){
                    $excludeTypes = array_merge($excludeTypes, $filterParams['exclude_defs'][$type]);
                }
            }
            if($filterParams['is_order_filter'] && isset($filterParams['total_price']) && SaleEnum::SR_TYPE_ORDER == $type){
                list($price, ) = explode(',', $rule['sr_caculate_params']);
                if($price <= $filterParams['total_price'] && $price > $maxPrice){
                    $maxPrice = $price;
                    $excludeIds[] = $maxPriceId;
                    $maxPriceId = $rule['sr_id'];
                }else{
                    $excludeIds[] = $rule['sr_id'];
                }
            }
        }

        $result = [];
        foreach($rules as $key => $rule){
            $exclude = in_array($rule['sr_object_type'], $excludeTypes)
                ||
                in_array($rule['sr_id'], $excludeIds)
                ;
            if(!$exclude){
                $result[] = $rule;
            }
        }
        return $result;
    }

    public static function find(){
        return SaleRule::find();
    }

    public function createSaleRule($data){
        $rule = new SaleRule();
        if(!$rule->load($data, '') || !$rule->validate()){
            $this->addErrors($rule->getErrors());
            return false;
        }
        $rule->insert(false);
        return $rule;
    }

    public function updateSaleRule($data, $rule){
        if(!$rule->load($data, '') || !$rule->validate()){
            $this->addErrors($rule->getErrors());
            return false;
        }
        $rule->update(false);
        return $rule;
    }


}