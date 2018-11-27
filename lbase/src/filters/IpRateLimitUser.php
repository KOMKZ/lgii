<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 上午11:06
 */
namespace lbase\filters;

use Yii;
use yii\base\Object;
use yii\filters\RateLimitInterface;

class IpRateLimitUser implements RateLimitInterface{
    public $rateLimit;
    public $rateLimitPer;
    public $ip;
    public function getRateLimit($request, $action)
    {
        return [$this->rateLimit, $this->rateLimitPer]; // $rateLimit requests per second
    }

    public function loadAllowance($request, $action)
    {
        $result = Yii::$app->cache->get($this->ip);
        if(false === $result){
            $result = [$this->rateLimit, time()];
            $r = Yii::$app->cache->set($this->ip, $result);
        }
        return $result;
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        Yii::$app->cache->set($this->ip, [$allowance, $timestamp]);
    }
}