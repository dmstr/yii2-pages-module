<?php

namespace dmstr\modules\pages\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;

/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PageUrlRule extends Object implements UrlRuleInterface
{

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'pages/default/page' && isset($params['pageId']) && isset($params['pageSlug'])) {

            $pagePath = (isset($params['pagePath']))?$params['pagePath'].'/':'';

            return 'p/'.$pagePath.$params['pageSlug'].'-'.$params['pageId'].'.html';
        }

        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        return false;  // this rule does not apply
    }
}