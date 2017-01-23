<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$common = [
    'id'          => 'test',
    'vendorPath'  => '@app/../vendor',
    'runtimePath' => '@app/../runtime',
    'aliases'     => [
        'dmstr/modules/pages' => '@vendor/dmstr/yii2-pages-module',
        'tests'               => '@vendor/dmstr/yii2-pages-module/tests',
        'backend'             => '@vendor/dmstr/yii2-backend-module/src',
    ],
    'components'  => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'db'          => [
            'class'             => 'yii\db\Connection',
            'dsn'               => getenv('DATABASE_DSN'),
            'username'          => getenv('DATABASE_USER'),
            'password'          => getenv('DATABASE_PASSWORD'),
            'charset'           => 'utf8',
            'tablePrefix'       => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
        ],
        'i18n'        => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'urlManager'  => [
            'class'                        => 'codemix\localeurls\UrlManager',
            'enablePrettyUrl'              => true,
            'showScriptName'               => getenv('YII_ENV_TEST') ? true : false,
            'scriptUrl'                    => (PHP_SAPI === 'cli') ? '/' : null,
            'enableDefaultLanguageUrlCode' => true,
            'baseUrl'                      => '/',
            'rules'                        => [
                'site/login' => 'user/security/login'
            ],
            'languages'                    => ['de'],
        ],
        'user'        => [
            'class'         => '\dmstr\web\User',
            'identityClass' => 'dektrium\user\models\User',
            'rootUsers'     => ['admin']
        ],
    ],
    'modules'     => [
        'pages'       => [
            'class' => 'dmstr\modules\pages\Module',
            'layout' => '@backend/views/layouts/main',
        ],
        'treemanager' => [
            'class'            => 'kartik\tree\Module',
            'treeViewSettings' => [
                'nodeView'    => '@vendor/dmstr/yii2-pages-module/views/treeview/_form',
                'fontAwesome' => true,
            ],
        ],
        'user'        => [
            'class' => '\dektrium\user\Module'
        ]
    ],
    'params'      => [
        'yii.migrations' => [
            '@vendor/dektrium/yii2-user/migrations',
            '@vendor/yiisoft/yii2/rbac/migrations',
            '@vendor/bedezign/yii2-audit/src/migrations',
            '@vendor/pheme/yii2-settings/migrations',
            '@vendor/dmstr/yii2-prototype-module/src/migrations',
            '@vendor/dmstr/yii2-pages-module/tests/codeception/migrations',
        ]
    ]
];

$web = [
    'components' => [
        'settings' => [
            'class' => '\pheme\settings\components\Settings'
        ],
        'request' => [
            'cookieValidationKey' => 'FUNCTIONAL_TESTING'
        ],
    ],
    'modules' => [
        'audit' => [
            'class' => '\bedezign\yii2\audit\Audit'
        ],
        'backend'          => [
            'class'  => 'app\modules\backend\Backend',
            'layout' => '@backend/views/layouts/main',
        ],
    ]
];

$console = [
    'components'    => [
        'urlManager' => [
            'scriptUrl' => '/',
        ],
    ],
    'controllerMap' => [
        'db'      => '\dmstr\console\controllers\MysqlController',
        'migrate' => '\dmstr\console\controllers\MigrateController'
    ],
];

return \yii\helpers\ArrayHelper::merge($common, (PHP_SAPI === 'cli') ? $console : $web);