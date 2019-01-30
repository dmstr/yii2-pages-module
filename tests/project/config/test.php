<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use app\components\EditorIdentity;
use yii\console\controllers\MigrateController;
use yii\rbac\PhpManager;
use yii\web\Application;

// prefer autoloaded classes from tests/project
# TODO: cleanup autoloading
#$testVendorPath = '/repo/tests/project/vendor';
#require($testVendorPath.'/autoload.php');

Yii::$classMap['dmstr\modules\pages\Module'] = '/repo/Module.php';
Yii::$classMap['app\components\EditorIdentity'] = '/repo/tests/project/src/components/EditorIdentity.php';

$common = [
    'id' => 'test',
    'vendorPath' => '/repo/tests/project/vendor',
    'runtimePath' => '@app/../runtime',
    'language' => 'de',
    'aliases' => [
        'repo' => '/repo',
        'dmstr/modules/pages' => '/repo',
        'vendor/dmstr/yii2-pages-module' => '/repo',
        'tests' => '@repo/tests',
        #'backend'             => '@vendor/dmstr/yii2-backend-module/src',
    ],
    'components' => [
        'request' => array(
            'enableCsrfValidation' => false,
        ),
        'cache' => \yii\caching\DummyCache::class,
        'authManager' => [
            'class' => PhpManager::class,
            'itemFile' => '@repo/tests/project/config/rbac/items.php',
            'assignmentFile' => '@repo/tests/project/config/rbac/assignments.php',
            'ruleFile' => '@repo/tests/project/config/rbac/rules.php',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DATABASE_DSN'),
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix' => 'test_' . getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'settings' => [
            'class' => '\pheme\settings\components\Settings',
        ],
        'user' => [
            'class' => 'dmstr\web\User',
            'identityClass' => 'app\components\EditorIdentity',
        ],
    ],
    'modules' => [
        'audit' => [
            'class' => 'bedezign\yii2\audit\Audit',
            'accessRoles' => ['audit-module'],
            'layout' => '@backend/views/layouts/box',
            'panels' => [
                'audit/request',
                'audit/mail',
                'audit/trail',
                'audit/javascript', # enable app.assets.registerJSLoggingAsset via settings
                // These provide special functionality and get loaded to activate it
                'audit/error',      // Links the extra error reporting functions (`exception()` and `errorMessage()`)
                'audit/extra',      // Links the data functions (`data()`)
                'audit/curl',       // Links the curl tracking function (`curlBegin()`, `curlEnd()` and `curlExec()`)
                //'audit/db',
                //'audit/log',
                //'audit/profiling',
            ],
            'ignoreActions' => [
                (getenv('APP_AUDIT_DISABLE_ALL_ACTIONS') ? '*' : '_'),
                'app/*',
                'audit/*',
                'help/*',
                'gii/*',
                'asset/*',
                'debug/*',
                'app/*',
                'resque/*',
                'db/create',
                'migrate/up',
            ],
            'maxAge' => 7,
        ],
        'pages' => [
            'class' => 'dmstr\modules\pages\Module',
            'layout' => '@app/views/layouts/main',
        ],
        'settings' => [
            'class' => '\pheme\settings\Module',
        ],
        'treemanager' => [
            'class' => 'kartik\tree\Module',
            'treeViewSettings' => [
                'nodeView' => '@vendor/dmstr/yii2-pages-module/views/treeview/_form',
                'fontAwesome' => true,
            ],
        ],
        /* 'user'        => [
             'class' => '\dektrium\user\Module'
         ]*/
    ],
    'params' => [
        'yii.migrations' => [
            '@vendor/dektrium/yii2-user/migrations',
            '@vendor/yiisoft/yii2/rbac/migrations',
            '@vendor/bedezign/yii2-audit/src/migrations',
            '@vendor/pheme/yii2-settings/migrations',
            '@vendor/dmstr/yii2-prototype-module/src/migrations',
            '@vendor/dmstr/yii2-pages-module/migrations',
            '@vendor/dmstr/yii2-pages-module/tests/migrations',
        ],
    ],
];

$web = [
    'on ' . Application::EVENT_BEFORE_REQUEST => function () {
        Yii::$app->user->login(new EditorIdentity());
    },
    'bootstrap' => [
        'debug',
    ],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            // allow all private IPs by default
            'allowedIPs' => [
                '127.0.0.1',
                '::1',
                '10.*',
                '192.168.*',
                '172.16.*',
                '172.17.*',
                '172.18.*',
                '172.19.*',
                '172.20.*',
                '172.21.*',
                '172.22.*',
                '172.23.*',
                '172.24.*',
                '172.25.*',
                '172.26.*',
                '172.27.*',
                '172.28.*',
                '172.29.*',
                '172.30.*',
                '172.31.*',
            ],
        ],
    ],
];

$console = [
    'components' => [
        'urlManager' => [
            'scriptUrl' => '/',
        ],
    ],
    'controllerMap' => [
        'db' => '\dmstr\console\controllers\MysqlController',
        'migrate' => [
            'class' => MigrateController::class,
            'migrationPath' => [
                '@dmstr/modules/pages/migrations',
                '@pheme/settings/migrations',
                '@bedezign/yii2/audit/migrations',
                '@tests/migrations',
            ],
        ],
        'copy-pages' => '\dmstr\modules\pages\commands\CopyController',
    ],
];

return \yii\helpers\ArrayHelper::merge($common, (PHP_SAPI === 'cli') ? $console : $web);