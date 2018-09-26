<?php
$localPay = require __DIR__ . '/pay-local.php';

return array_merge([
    'alipay' => [
        'class' => '\\lgoods\models\\trans\\payment\\Alipay',
        'gatewayUrl' => '',
        'appId' => '',
        'rsaPrivateKeyFilePath' => '',
        'alipayrsaPublicKey' => '',
        'notifyUrl' => '',
        'returnUrl' => '',
    ],
    'wxpay' => [
        'class' => '\\lgoods\models\\trans\\payment\\Wxpay',
        'appid' => '',
        'mchid' => '',
        'key' => '',
        'appsecret' => '',
        'sslcert_path' => '',
        'sslkey_path' => '',
    ],
],$localPay);