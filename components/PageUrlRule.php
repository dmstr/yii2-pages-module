<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\components;

use \dmstr\modules\pages\models\Tree;
use yii\web\UrlRuleInterface;
use yii\base\Object;

/**
 * Class PageUrlRule
 * @package dmstr\modules\pages\components
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class PageUrlRule extends Object implements UrlRuleInterface
{
    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     *
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route === Tree::DEFAULT_PAGE_ROUTE) {

            /**
             * Build page url
             */
            $pageId = '';
            if (isset($params[Tree::REQUEST_PARAM_ID])) {
                $pageId = '-' . $params[Tree::REQUEST_PARAM_ID];
                unset($params[Tree::REQUEST_PARAM_ID]);
            }

            $pageSlug = '';
            if (isset($params[Tree::REQUEST_PARAM_SLUG])) {
                $pageSlug = $params[Tree::REQUEST_PARAM_SLUG];
                unset($params[Tree::REQUEST_PARAM_SLUG]);
            }

            $pagePath = '';
            if (isset($params[Tree::REQUEST_PARAM_PATH])) {
                $pagePath = $params[Tree::REQUEST_PARAM_PATH] . '/';
                unset($params[Tree::REQUEST_PARAM_PATH]);
            }

            $pageUrl = $pagePath . $pageSlug . $pageId;

            /**
             * Add additional request params if set
             */
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $pageUrl .= '?' . $query;
            }

            return $pageUrl;
        }

        return false;  // this rule does not apply
    }

    /**
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request $request
     *
     * @return bool
     */
    public function parseRequest($manager, $request)
    {
        return false;  // this rule does not apply
    }
}
