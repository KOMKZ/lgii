<?php
use yii\base\Event;
use lgoods\models\goods\GoodsModel;
Yii::setAlias('@lgii', dirname(__DIR__) . "/lgii/src");
Yii::setAlias('@lgoods', dirname(__DIR__) . '/lgoods/src');
Event::on("\lgoods\models\goods\GoodsModel", GoodsModel::EVENT_GOODS_CREATE, ["\lgoods\models\goods\GoodsModel", 'handleGoodCreate']);

require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Config.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Exception.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Data.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Notify.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Api.php');
require(dirname(__DIR__) . '/lib/alisdk/alipay/AopSdk.php');
