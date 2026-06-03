<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'app-frontend',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass'  => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
            'loginUrl'       => ['/site/login'],
        ],
        'session' => [
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class'  => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    // Acceso global: todos los módulos del frontend requieren login
    // excepto site/login, site/signup y site/error
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/login', 'site/signup', 'site/error',
                     'site/request-password-reset', 'site/reset-password',
                     'site/verify-email', 'site/resend-verification-email'],
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],   // cualquier usuario autenticado
            ],
        ],
    ],
    'params' => $params,
];
