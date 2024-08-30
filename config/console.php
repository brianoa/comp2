<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$mpesa_db = require __DIR__ . '/mpesa_db.php';
$sms_db = require __DIR__ . '/sms_db.php';
$analytics_db = require __DIR__ . '/analytics_db.php';
$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log','queue','analytics_queue'],
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
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'myhelper'        => [
            'class' => 'app\components\Myhelper',
        ],
        'db' => $db,
        'mpesa_db' => $mpesa_db,
        'sms_db' => $sms_db,
        'analytics_db'=>$analytics_db,
        'queue' =>  [
            'class' => \yii\queue\db\Queue::class,
            'db' => $sms_db, // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => [
                'class'=>\yii\mutex\MysqlMutex::class, // Mutex used to sync queries
                'db' => $sms_db // DB connection component or its config
            ],
            'ttr' => 43200
        ],
        'analyticsqueue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => $sms_db, // DB connection component or its config
            'tableName' => '{{%analytics_queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => [
                'class' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
                'db' => $sms_db // DB connection component or its config
            ],
            'ttr' => 43200,
        ],
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
