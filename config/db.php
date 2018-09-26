<?php

return array_merge([
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2pro',
    'username' => 'root',
    'password' => '123456',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
], require __DIR__ . '/db-local.php');
