<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages;

use dmstr\modules\pages\components\PageUrlRule;
use dmstr\modules\pages\models\Tree;
use yii\base\Application;
use yii\base\BootstrapInterface;
use kartik\tree\Module As TreeModule;

/**
 * Class Bootstrap
 *
 * @package dmstr\modules\pages
 * @author Marc Mautz <marc@diemeisterei.de>
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        // register migration
        $app->params['yii.migrations'][] = '@vendor/dmstr/yii2-pages-module/migrations';

        // register module
        if (\Yii::$app->hasModule('pages') && !\Yii::$app->hasModule('treemanager')) {
            $app->setModule(
                'treemanager',
                [
                    'class' => TreeModule::class,
                    'layout' => '@admin-views/layouts/main',
                    'treeViewSettings' => [
                        'nodeView' => '@vendor/dmstr/yii2-pages-module/views/treeview/_form',
                        'fontAwesome' => true,
                    ],

                ]
            );
        }

        // provide default page url rule
        $app->urlManager->addRules(
            [
                // pages default page route
                ['class' => PageUrlRule::class],
                [
                    'pattern' => 'p/<' . Tree::REQUEST_PARAM_PATH . ':[a-zA-Z0-9_\-\./\+]*>/<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>.html',
                    'route' => 'pages/default/page',
                    'encodeParams' => false,
                ],
                'p/<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>.html' => 'pages/default/page',

                // Backward compatibility
                'page/<' . Tree::REQUEST_PARAM_PATH . ':[a-zA-Z0-9_\-\./\+]*>/<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>.html' => 'pages/default/page',
                'page/<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>.html' => 'pages/default/page',
                '<' . Tree::REQUEST_PARAM_PATH . ':[a-zA-Z0-9_\-\./\+]*>/<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>' => 'pages/default/page',
                '<' . Tree::REQUEST_PARAM_SLUG . ':[a-zA-Z0-9_\-\.]*>-<' . Tree::REQUEST_PARAM_ID . ':[0-9]*>' => 'pages/default/page',
            ]);
    }
}
