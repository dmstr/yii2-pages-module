<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages;

use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Class Bootstrap
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
        if (!\Yii::$app->hasModule('pages')) {
            $app->setModule(
                'pages',
                [
                    'class' => 'dmstr\modules\pages\Module'
                ]
            );
        }

        // provide default page url rule
        $app->urlManager->addRules(
            [
                // pages default page route
                '<parentLeave:[a-zA-Z0-9_\- \.]*>/<pageName:[a-zA-Z0-9_\-\.]*>-<id:[0-9]*>' => 'pages/default/page',
                '<pageName:[a-zA-Z0-9_\-\.]*>-<id:[0-9]*>' => 'pages/default/page',
            ],
            true
        );
    }
}
