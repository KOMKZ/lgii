<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-22
 * Time: 下午9:53
 */
namespace lgoods\caculators;

class FullSub{
    CONST ID = 2;

    public function check($priceItems){
        return true;
    }
    public function caculate($priceItems){
        list($price, $discount) = explode(',', $priceItems['sr_caculate_params']);
        return $discount;
    }
}