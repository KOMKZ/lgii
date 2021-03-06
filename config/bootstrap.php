<?php
use yii\base\Event;
use lgoods\models\goods\GoodsEnum;
use lgoods\models\trans\TransEnum;
use lgoods\models\trans\Trans;
use lgoods\models\trans\TransModel;
Yii::setAlias('@lbase', dirname(__DIR__) . '/lbase/src');
Yii::setAlias('@ldebug', dirname(__DIR__) . "/ldebug/src");
Yii::setAlias('@lgii', dirname(__DIR__) . "/lgii/src");
Yii::setAlias('@lgoods', dirname(__DIR__) . '/lgoods/src');
Yii::setAlias('@lfile', dirname(__DIR__) . '/lfile/src');
Yii::setAlias('@lsite', dirname(__DIR__) . '/lsite/src');
Yii::setAlias('@luser', dirname(__DIR__) . '/luser/src');
Yii::setAlias('@OSS', dirname(__DIR__) . '/lib/alisdk/OSS');
Yii::$container->set('yii\data\Pagination', [
    // 不限制页面的数量, per-page = -1时代表取全部
    'pageSizeLimit' => [1]
]);
require Yii::getAlias("@lsite/models/action/action.php");
require Yii::getAlias("@lbase/helpers/func.php");


Event::on("\lgoods\models\goods\GoodsModel", GoodsEnum::EVENT_GOODS_CREATE, ["\lgoods\models\goods\GoodsModel", 'handleGoodCreate']);
Event::on("\lgoods\models\\trans\PayTrace", TransEnum::EVENT_AFTER_PAYED, ["\lgoods\models\\trans\TransModel", "handleReceivePayedEvent"]);
Event::on("\lgoods\models\\trans\PayTrace", TransEnum::EVENT_AFTER_RFED, ["\lgoods\models\\trans\TransModel", "handleReceiveRfedEvent"]);
Event::on("\lgoods\models\\trans\Trans", TransEnum::EVENT_TRS_AFTER_PAYED, ["\lgoods\models\order\OrderModel", "handleReceivePayedEvent"]);
Event::on("\lgoods\models\\trans\Trans", TransEnum::EVENT_TRS_AFTER_RFED, ["\lgoods\models\\refund\RfModel", "handleReceiveRfedEvent"]);


require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Config.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Exception.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Data.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Notify.php');
require(dirname(__DIR__) . '/lib/wxsdk/wxpay/lib/WxPay.Api.php');
require(dirname(__DIR__) . '/lib/alisdk/alipay/AopSdk.php');
require(dirname(__DIR__) . '/lib/Spyc.php');

// fix bug https://github.com/auth0/auth0-PHP/issues/56
\Firebase\JWT\JWT::$leeway = 50;