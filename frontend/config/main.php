<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'allow' => true,
                'controllers' => ['site','debug/default'],
                'roles' => ['?','@'],
            ],
            [
                'allow' => true,
                'actions' => ['index','view','get-book'],
                'controllers' => ['book'],
                'roles' => ['?','@'],
            ],
            [
                'allow' => true,
                'roles' => ['admin'],
            ],
            [
                'allow' => true,
                'actions' => ['index','view'],
                'controllers' => ['card'],
                'roles' => ['@'],
            ],
        ],
    ],
    'params' => $params,
];
