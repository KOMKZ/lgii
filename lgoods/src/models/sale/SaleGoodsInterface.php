<?php
namespace lgoods\models\sale;

interface SaleGoodsInterface{
    public function getOriginPrice();

    public function getLatestPrice();

    public function setLatestPrice($value);

    public function appendRuleHistory($ruleClass, $finalPrice, $params = []);

    public function listRuleHistory();

}
