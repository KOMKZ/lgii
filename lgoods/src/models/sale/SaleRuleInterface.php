<?php
namespace lgoods\models\sale;

interface SaleRuleInterface{
    public static function applyTo($goods, $history = []);
    public function getIsFinal();
}
