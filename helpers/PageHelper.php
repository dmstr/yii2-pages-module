<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\helpers;

use dmstr\modules\pages\traits\RequestParamActionTrait;
use yii\base\BaseObject;

/**
 * Class PageHelper
 * @package dmstr\modules\pages\helpers
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
class PageHelper
{
    /**
     * @param $route
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function routeToSchema($route)
    {
        $responseCluster = \Yii::$app->createController($route);

        if (isset($responseCluster[0])) {
            $controller = $responseCluster[0];
            /** @var BaseObject $controller */
            if ($controller->hasMethod('jsonFromAction')) {
                /** @var RequestParamActionTrait $controller */
                return $controller->jsonFromAction($route);
            }
        }
        return static::defaultJsonSchema();
    }

    /**
     * @return string
     */
    public static function defaultJsonSchema()
    {
        return <<<JSON
{
    "title": "Request Params",
    "type": "object"
}
JSON;

    }
}