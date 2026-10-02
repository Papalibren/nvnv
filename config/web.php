<?php

$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/db.php';

$config = [
    'id'       => 'ege-informatika',
    'name'     => 'Codenvi',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'timeZone' => 'Europe/Moscow',
    'charset'  => 'UTF-8',

    'bootstrap' => ['log'],

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [

        'request' => [
            'cookieValidationKey' => $params['cookieValidationKey'],
            'baseUrl' => '',
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl'        => ['/auth/login'],
            'idParam'         => '__id',
            'authTimeoutParam'=> '__expire',
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class'            => 'yii\symfonymailer\Mailer',
            'viewPath'         => '@app/mail',
            'useFileTransport' => YII_ENV_DEV,  // в dev пишет в файл, не отправляет
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        'db' => $db,

        'urlManager' => [
            'class'           => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => require __DIR__ . '/routes.php',
        ],

        'authManager' => [
            'class'          => 'yii\rbac\DbManager',
            'itemTable'      => 'auth_item',
            'itemChildTable' => 'auth_item_child',
            'assignmentTable'=> 'auth_assignment',
            'ruleTable'      => 'auth_rule',
        ],

        // Хранилище файлов — переключается одной строкой
        'storage' => [
            'class'    => 'app\components\storage\LocalStorage',
            'basePath' => '@app/storage',
            'baseUrl'  => '/files',
        ],

        'session' => [
            'class'   => 'yii\web\Session',
            'timeout' => 86400 * 30,  // 30 дней
        ],

    ],

    'params' => $params,

    // Пространства имён для контроллеров в подпапках
    'controllerMap' => require __DIR__ . '/controllers.php',
];

// Роутинг вложенных контроллеров через controllerNamespace не работает
// в basic — используем controllerMap, заполним позже через auto-discovery
// или явное перечисление. Сейчас оставляем пустым.

if (YII_ENV_DEV) {
    $config['bootstrap'][]  = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][]  = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;