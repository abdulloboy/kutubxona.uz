<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Sccy2ZncJXXqwyzMxZa8BA89aqHOFg5V',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '82.145.208.'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'myCrud' => [
                'class' => 'common\generators\crud\Generator',
            ],
            'myModel' => [
                'class' => 'common\generators\model\Generator',
            ],
        ],
    ];
}

return $config;
