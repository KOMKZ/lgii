<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-7
 * Time: 下午5:52
 */
namespace lgoods\models\sale;

use yii\base\Model;

class SaleModel extends Model{
    public function caculateGoodsPrice($goods, $rules){
        $ruleHistory = [];
        foreach($rules as $rule){
            if(!($rule instanceof SaleRuleInterface)){
                throw new \Exception("销售规则不合法");
            }
            $newPrice = $rule->applyTo($goods, $ruleHistory);
            $ruleName = $rule::className();
            $ruleHistory[$ruleName][] = [
                $newPrice, []
            ];
            if($rule->getIsFinal()){
                break;
            }
        }
        return [$newPrice, $ruleHistory];
    }

    public function caculateListPrice($goods, $rules, $refresh = false){
        // 使用静态值
    }

}