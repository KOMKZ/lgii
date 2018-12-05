<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-11-22
 * Time: 下午9:53
 */
namespace lgoods\caculators;

class Discount{
    const ID = 1;


    public function check($priceItems){
        return true;
    }
    public function caculate($priceItems){
        $discount = (10 - $priceItems['sr_caculate_params'])/10 * $priceItems['og_total_price'];
        return $discount;
    }
}