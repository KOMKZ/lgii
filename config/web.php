<?php
use yii\helpers\ArrayHelper;
require __DIR__ . '/bootstrap.php';

$configLocal = require __DIR__ . '/web-local.php';
$localParams = require __DIR__ . '/web-params-local.php';
$config = ArrayHelper::merge([
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
        ]
    ],
    'timeZone'=>'Asia/Shanghai',
    'controllerMap' => [
        'lgoods' => '\lgoods\controllers\LgoodsController',
        'lorder' => '\lgoods\controllers\LorderController',
        'ltrans' => '\lgoods\controllers\LtransController',
        'lrefund' => '\lgoods\controllers\LrefundController',
        'lfile' => '\lfile\controllers\LfileController',
        'lattr' => '\lgoods\controllers\LattrController',
        'lcollect' => '\lgoods\controllers\LcollectController',
        'lclassification' => '\lgoods\controllers\LclassificationController',
        'lcart-item' => '\lgoods\controllers\LcartItemController',
        'lsale-rule' => '\lgoods\controllers\LsaleRuleController',
        'luser' => '\luser\controllers\LuserController',
        'lauth' => '\luser\controllers\LauthController',
        'luser' => '\luser\controllers\LuserController',
        'lcoupon' => '\lgoods\controllers\LcouponController',
        'luser-coupon' => '\lgoods\controllers\LuserCouponController',
        'lbanner' => '\lsite\controllers\LbannerController',
        'laction' => '\lsite\controllers\LactionController',

    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'YajfqYjgy2inkKO1zMuKnDcdbh_vWf1D',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'alog' => [
            'class' => 'lsite\models\action\ActionComponent',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2pro',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'enableSchemaCache' =>  true,
            // Schema cache options (for production environment)
            //'enableSchemaCache' => true,
            //'schemaCacheDuration' => 60,
            //'schemaCache' => 'cache',
        ],
        'logdb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=logdb',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ],
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
            'notifyUrl' => ''
        ],
        'wxpay_app' => [
            'class' => '\\lgoods\models\\trans\\payment\\Wxpay',
            'appid' => '',
            'mchid' => '',
            'key' => '',
            'appsecret' => '',
            'sslcert_path' => '',
            'sslkey_path' => '',
            'notifyUrl' => '',
        ],
        'user' => [
            'identityClass' => 'luser\models\user\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'class' => "lbase\ErrorHandler",
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'apiurl' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'hostInfo' => 'http://yii2shop-api',
            'baseUrl' => '/lfile/output',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'OPTIONS <route:.*>' => "site/index",
                'trans_notification/<type:.*?>' => 'trans/notify',
                'lfile/output/?' => 'lfile/output',
                'auth/login/?' => 'lauth/login',
                'lorder/check/?' => 'lorder/check',


                'DELETE <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/<sub_index:[^\/]+>/?' => "<controller>/delete-<sub>",
                'GET <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/<sub_index:[^\/]+>/?' => "<controller>/view-<sub>",
                'PUT <controller:[\w\-:]+>/<index:[^\/]+>/<sub:[\w\-:]+>/<sub_index:[^\/]+>/?' => "<controller>/update-<sub>",
                'GET <controller:[\w\-:]+>/<index:[^\/]+>/?' => "<controller>/view",
                'GET <controller:[\w\-:]+>/?' => "<controller>/list",
                'DELETE <controller:[\w\-:]+>/<index:[^\/]+>/?' => "<controller>/delete",
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
            'base' => "/tmp",
            'host' => "http://yii2shop-api.com",
            "urlRoute" => "lfile/output",
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
        'file' => [
            'class' => '\\lfile\models\\FileModel'
        ]
    ],
    'params' => array_merge([
        'github_update_secret' => '',
        'jwt' => [
            'secret_key' => 'abc',
            'allow_algs' => ['HS512'],
            'encode_alg' => 'HS512'
        ],
        'api_behaviors_bootstrap' => [
            "rateLimiter" => 1,
            "corsFilter" => 1,
            "bearerAuth" => 1,
            "access" => 1,
        ],
        'api_behaviors' => [
            'rateLimiter' => [
                'class' => \lbase\filters\RateLimiter::className(),
                'rateLimit' => 360000,
                'rateLimitPer' => 60,
                'ignoreIps' => ["127.0.0.1"]
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to
                    'Origin' => ["http://localhost:8080"],
                    'Access-Control-Request-Method' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTIONS'],
                    // Allow only POST and PUT methods
                    'Access-Control-Request-Headers' => ['*'],
                    // Allow only headers 'X-Wsse'
                    'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                ],
            ],
            'bearerAuth' => [
                'class' => \lbase\filters\HttpBearerAuth::class,
                'optional' => ['auth/login', 'site/index', ]
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        // 权限控制器
                        'matchCallback' => function($rule, $action){
                            $authMg = Yii::$app->authManager;
                            $permName = $action->controller->id . '/' . $action->id;
                            $identity = Yii::$app->user->identity;
                            if(null === $identity
                                && in_array($permName, array_keys($authMg->getPermissionsByRole('vistor')))
                            ){
                                return true;
                            }
                            if(null !== $identity
                                && $authMg->checkAccess($identity->u_id, $permName)
                            ){
                                return true;
                            }
                            return false;
                        }
                    ]
                ]
            ]
        ]
    ], $localParams),
], $configLocal);


return $config;
