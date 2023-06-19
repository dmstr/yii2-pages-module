<?php
// @group mandatory

use Codeception\Util\Debug;
use dmstr\modules\pages\components\PageUrlRule;
use dmstr\modules\pages\models\Tree;

class UrlTestCase extends \Codeception\Test\Unit
{
    /**
     * base params tests
     */
    public function testUrlRuleParams()
    {
        // Manager setup
        $urlManager = \Yii::$app->urlManager;
        $urlManager->enablePrettyUrl = 1;
        $urlManager->showScriptName = false;

        // Pages rule globals
        $rule = new PageUrlRule();
        $route = '/pages/default/page';

        /**
         * Check url with params
         *  - pageId
         */
        $params = [Tree::REQUEST_PARAM_ID => 1];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = '-1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url with params
         *  - pageId
         *  - pageSlug
         */
        $params = [Tree::REQUEST_PARAM_ID => 1, Tree::REQUEST_PARAM_SLUG => 'slug'];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = 'slug-1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url with params
         *  - pageId
         *  - pageSlug
         *  - pagePath
         */
        $params = [Tree::REQUEST_PARAM_ID => 1, Tree::REQUEST_PARAM_SLUG => 'slug', Tree::REQUEST_PARAM_PATH => 'subpage/next-subpage/next-subpage'];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = 'subpage/next-subpage/next-subpage/slug-1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url for static routes without params
         *  - pageId
         */
        $params = [0 => '/static-route'];

        $createdUrl = $urlManager->createUrl($params);
        $expectedUrl = '/static-route';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url for static routes with params
         *  - param1 => value1
         */
        $params = [0 => '/static-route', 'param1' => 'value1'];

        $createdUrl = $urlManager->createUrl($params);
        $expectedUrl = '/static-route?param1=value1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * add URL rule
         */
        $urlManager->addRules(
            [
                '/static-route/<param1:[a-zA-Z0-9_\-\.]*>-<pageId:[0-9]*>.html' => 'static-route',
            ]
        );

        /**
         * Check url for static routes with params
         *  - pageId => 5
         *  - param1 => value1
         */
        $route = 'static-route';
        $params = [Tree::REQUEST_PARAM_ID => 5, 'param1' => 'value1'];
        $createdUrl = $rule->createUrl($urlManager, $route, $params);

        /**
         * if not pages/default/page route, the PageUrlRule will not match
         * and the application url manager will be used
         */
        if ($createdUrl === false) {
            $params = [0 => '/static-route', Tree::REQUEST_PARAM_ID => 5, 'param1' => 'value1'];

            $createdUrl = $urlManager->createUrl($params);
        }
        $expectedUrl = '/static-route/value1-5.html';

        $this->assertEquals($expectedUrl, $createdUrl);
    }
}
