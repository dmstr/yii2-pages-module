<?php

return [
    'id' => 'test',
    'basePath' => '/app/src',
    'runtimePath' => '/app/runtime',
    'aliases' => [
        '@dmstr/modules/pages' => '@vendor/dmstr/yii2-pages-module',
        '@tests' => '@vendor/dmstr/yii2-pages-module/tests',
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=db;dbname=test',
            'username' => 'test',
            'password' => 'test',
            'charset' => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true
        ],
        'user' => [
            'class' => 'dmstr\modules\pages\tests\_web\TestUser',
            'identityClass' => 'dektrium\user\models\User',
        ],
    ],
    'modules' => [
        'pages' => [
            'class' => 'dmstr\modules\pages\Module',
            'availableRoutes' => [
                'pages/default/page' => 'pages/default/page'
            ],
            'availableViews' => [
                '@dmstr/modules/pages/example-views/column1' => '@dmstr/modules/pages/example-views/column1'

            ],
            #'layout' => '@admin-views/layouts/main',
        ],

        'treemanager' =>
            [
                'class' => 'kartik\tree\Module',
                #'layout' => '@admin-views/layouts/main',
                'treeViewSettings' => [
                    'nodeView' => '@vendor/dmstr/yii2-pages-module/views/treeview/_form',
                    'fontAwesome' => true,
                ],

            ]
    ],
    'params' => [
        'yii.migrations' => [
            '@vendor/dmstr/yii2-pages-module/migrations'
        ]
    ]
];
