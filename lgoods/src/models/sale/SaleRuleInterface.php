<?php
namespace lgoods\models\sale;

interface SaleRuleInterface{
    public static function applyTo(SaleGoodsInterface $goods);

}
