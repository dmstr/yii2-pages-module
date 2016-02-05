<?php


return [
    'aliases' => [
        '@dmstr/modules/pages' => '@vendor/dmstr/yii2-pages-module',
        '@tests' => '@vendor/dmstr/yii2-pages-module/tests'
    ],
    'components' => [
        'db' => [
            'tablePrefix' => 'app_',
        ],
    ],
    'modules' => [
        'pages' => [
            'class' => 'dmstr\modules\pages\Module',
            'layout' => '@admin-views/layouts/main',
        ],
    ],
    'params' => [
        'yii.migrations' => [
            '@vendor/dmstr/yii2-pages-module/migrations'
        ]
    ]
];