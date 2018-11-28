<?php
use yii\helpers\ArrayHelper;
require __DIR__ . '/bootstrap.php';

$configLocal = require __DIR__ . '/console-local.php';

$params = [
    'apialias' => [],
    'apides' => [],
    'apifiles' => [
        'all' => [
            '@app/config/swagger-root.php',
            '@app/lgoods/src/controllers/lgoodsController.php',
            '@app/lgoods/src/controllers/LcollectController.php',
            '@app/lgoods/src/controllers/LsaleRuleController.php',
            '@app/lgoods/src/controllers/LorderController.php',
            '@app/lgoods/src/controllers/LclassificationController.php',
            '@app/lsite/src/controllers/LbannerController.php',
            '@app/lfile/src/controllers/LfileController.php',

        ],
    ]
];

$config = ArrayHelper::merge([
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'es' => \Elasticsearch\ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build(),
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
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
        ]
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
], $configLocal);

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
