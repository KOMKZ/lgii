<?php
use yii\base\Event;
use lgoods\models\goods\GoodsModel;
use lgoods\models\trans\PayTrace;
use lgoods\models\trans\Trans;
use lgoods\models\trans\TransModel;

Yii::setAlias('@ldebug', dirname(__DIR__) . "/ldebug/src");
Yii::setAlias('@lgii', dirname(__DIR__) . "/lgii/src");
Yii::setAlias('@lgoods', dirname(__DIR__) . '/lgoods/src');
Event::on("\lgoods\models\goods\GoodsModel", GoodsModel::EVENT_GOODS_CREATE, ["\lgoods\models\goods\GoodsModel", 'handleGoodCreate']);
Event::on("\lgoods\models\\trans\PayTrace", PayTrace::EVENT_AFTER_PAYED, ["\lgoods\models\\trans\TransModel", "handleReceivePayedEvent"]);
Event::on("\lgoods\models\\trans\PayTrace", PayTrace::EVENT_AFTER_RFED, ["\lgoods\models\\trans\TransModel", "handleReceiveRfedEvent"]);
Event::on("\lgoods\models\\trans\Trans", Trans::EVENT_AFTER_PAYED, ["\lgoods\models\order\OrderModel", "handleReceivePayedEvent"]);
Event::on("\lgoods\models\\trans\Trans", Trans::EVENT_AFTER_RFED, ["\lgoods\models\order\OrderModel", "handleReceiveRfedEvent"]);


require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Config.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Exception.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Data.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Notify.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Api.php');
require(dirname(__DIR__) . '/lib/alisdk/alipay/AopSdk.php');
