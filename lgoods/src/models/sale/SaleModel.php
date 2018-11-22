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

    public static function fetchTargetRules($range){
        $rules = [];
        $query = static::find();
        foreach($range as $rangeCond){
            $query->orWhere($rangeCond);
        }
        $query->andWhere(['=', 'sr_status', SaleRule::SR_STATUS_VALID]);
        $query->andWhere(['<=', 'sr_start_at', time()]);
        $query->andWhere(['>=', 'sr_end_at', time()]);
        $rules = $query->all();
        return $rules;
    }

    public static function filterRules($rules, $filterParams = []){
        $excludeTypes = [];
        if($filterParams['exclude_defs']){
            $excludeTypes = [];
            foreach($rules as $rule){
                $type = $rule['sr_object_type'];
                if(isset($filterParams['exclude_defs'][$type])){
                    $excludeTypes = array_merge($excludeTypes, $filterParams['exclude_defs'][$type]);
                }
            }
        }
        $result = [];
        foreach($rules as $key => $rule){
            if(!in_array($rule['sr_object_type'], $excludeTypes)){
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