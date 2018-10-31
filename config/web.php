<?php
require __DIR__ . '/bootstrap.php';

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$pay = require __DIR__ . '/pay.php';

$components = array_merge([
    'request' => [
        // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
        'cookieValidationKey' => 'YajfqYjgy2inkKO1zMuKnDcdbh_vWf1D',
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'user' => [
        'identityClass' => 'app\models\User',
        'enableAutoLogin' => true,
    ],
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'rules' => [
            'OPTIONS <route:.*>' => "site/index",
            'trans_notification/<type:.*?>' => 'trans/notify',
            'file/output' => 'file/output',


            'GET <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/<sub_index:[^\/]+>/?' => "<controller>/view-<sub>",
            'PUT <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/<sub_index:[^\/]+>/?' => "<controller>/update-<sub>",
            'GET <controller:[\w\-:]+>/<index:[^\/]+>/?' => "<controller>/view",
            'GET <controller:[\w\-:]+>/?' => "<controller>/list",
            'GET <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/?' => "<controller>/list-<sub>",
            'POST <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/?' => '<controller>/create-<sub>',
            'POST <controller:[\w\-:]+>/?' => "<controller>/create",
            'PUT <controller:[\w\-:]+>/<index:[^\/]+>/?' => '<controller>/update'

        ],
    ],
    'response' => [
        'format' => yii\web\Response::FORMAT_JSON,
        'charset' => 'UTF-8',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        // send all mails to a file by default. You have to set
        // 'useFileTransport' to false and configure a transport
        // for the mailer to send real emails.
        'useFileTransport' => true,
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'filedisk' => [
        'class' => "\\lfile\\models\\drivers\\Disk",
        'base' => "",
        'host' => "",
        "urlRoute" => "",
        'dirMode' => 0777,
        'fileMode' => 0777,
    ],
    'fileoss' => [
        'class' => '\\lfile\\models\\drivers\\Oss',
        'access_key_id' => '',
        'access_secret_key' => '',
        'timeout' => 60,
        'bucket_cans' => [],
    ],
    'db' => $db,
    'logdb' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=logdb',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
    ]
], $pay);


$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone'=>'Asia/Shanghai',
    'controllerMap' => [
        'goods' => '\lgoods\controllers\GoodsController',
        'lorder' => '\lgoods\controllers\LorderController',
        'ltrans' => '\lgoods\controllers\LtransController',
        'lrefund' => '\lgoods\controllers\LrefundController',
        'lfile' => '\lfile\controllers\LfileController',
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => $components,
    'params' => $params,
];

if (1) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => '\yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            ['class' => "\lgii\generators\model\Generator"]
        ]
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
