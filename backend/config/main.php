<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'app-backend',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap'           => ['log'],
    'modules'             => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass'  => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl'       => ['/site/login'],
        ],
        'session' => [
            'name' => 'advanced-backend',
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
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/login', 'site/error'],
        'rules' => [
            [
                'allow' => true,
                'matchCallback' => function ($rule, $action) {
                    return !Yii::$app->user->isGuest
                        && Yii::$app->user->identity->role_id === \common\models\User::ROL_ADMIN;
                },
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            if (Yii::$app->user->isGuest) {
                return Yii::$app->response->redirect(['/site/login']);
            }

            $baseUrl = Yii::$app->request->baseUrl;
            $frontendLoginUrl = strpos($baseUrl, '/backend/web') !== false
                ? str_replace('/backend/web', '/frontend/web', $baseUrl) . '/index.php?r=site%2Flogin'
                : '/gestor_expediente_1/frontend/web/index.php?r=site%2Flogin';

            Yii::$app->user->logout();
            return Yii::$app->response->redirect($frontendLoginUrl);
        },
    ],
    'params' => $params,
];
