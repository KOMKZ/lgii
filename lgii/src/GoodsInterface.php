<?php
/**
 * Created by PhpStorm.
 * User: lartik
 * Date: 18-8-31
 * Time: 上午12:22
 */
namespace lgii;

interface GoodsInterface{


    public static function updateSkus(Array $data);

    public static function createSkus(Array $data);

    public static function loacateSku(Array $params);

    public static function caculatePrice(Array $params);

    public static function getSkuAttrs();

    public static function getSkuIds(Array $params = []);

    public static function getSkus();


}