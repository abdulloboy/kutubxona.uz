<?php
return [
    'bootstrap' => ['gii'],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:../../../ishchi/tayyor/webDastur/MO/adminer/biblio.db',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
];
