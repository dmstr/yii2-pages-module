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
    }
}
