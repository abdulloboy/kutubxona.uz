<?php

return [
    'bootstrap' => ['log'],
    'defaultRoute' => 'book',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'assignmentFile' => '@common/rbac/assignments.php',
            'defaultRoles' => ['reader'],
            'itemFile' => '@common/rbac/items.php',
            'ruleFile' => '@common/rbac/rules.php',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'formatter' => [
            'dateFormat' => 'php:Y-m-d', 
            'timeFormat' => 'php:H:i:s', 
            'datetimeFormat' => 'php:Y-m-d H:i:s',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'myYii\log\DbTarget',
                    'categories' => ['yii\web\User*',
                      'application*'],
                    'levels' => ['info'],
                ],
            ],
        ],
    ],
    'modules' => [
        'datecontrol' =>  [
            'class' => 'kartik\datecontrol\Module',
            'displaySettings' => [ 
                'date' => 'php:dd-MM-yyyy', 
                'time' => 'php:HH:mm:ss', 
                'datetime' => 'php:Y-m-d H:i:s', 
            ],
            'saveSettings' => [ 
                'date' => 'php:U',
                'time' => 'php:U',
                'datetime' => 'php:U', 
            ],
    //        'displayTimezone' => '5',
     //       'saveTimezone' => 'UTC',
    //        'autoWidget' => true,
            'ajaxConversion' => false,
   /*         'autoWidgetSettings' => [ 
                'datetime' => [
                    'type'=>2, 
                    'pluginOptions'=>[
                        'autoclose'=>true
                    ],
                ],
            'time' => [],
            'datetime' => [],
            ],
            'widgetSettings' => [ 
                'datetime' => [
                    'class' => 'yii\jui\DatePicker',
                    'options' => [
                        'dateFormat' => 'php:d-M-Y',
                        'options' => [
                            'class'=>'form-control'
                        ], 
                    ],
                ],
            ],*/
        ],
    ],
];
