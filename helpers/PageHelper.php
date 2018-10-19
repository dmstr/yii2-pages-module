<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\helpers;

use dmstr\modules\pages\interfaces\PageTreeRouteItems;

/**
 * Class PageHelper
 * @package dmstr\modules\pages\helpers
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
class PageHelper
{
    /**
     * @param $route
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public static function routeToSchema($route)
    {
        $answerCluster = \Yii::$app->createController($route);

        /** @var Controller $controller */
        if (isset($answerCluster[0])) {
            $controller = $answerCluster[0];

            if ($controller instanceof PageTreeRouteItems) {
                return $controller->getPageTreeRouteItems($route);
            }
        }
        return '{"type": "opject"}';
}
}