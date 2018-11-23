<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-23
 * Time: 上午9:27
 */
namespace lgoods\helpers;

class PriceHelper{
    public static function format($value){
        return sprintf('%0.2f', $value/100);
    }
}