<?php

namespace dmstr\modules\pages\tests\unit;

use Codeception\Util\Debug;
use dmstr\modules\pages\components\PageUrlRule;

class UrlTestCase extends \Codeception\Test\Unit
{
    public $appConfig = '/app/src/config/main.php';

    /**
     * base params tests
     */
    public function testUrlRuleParams()
    {
        // Manager setup
        $urlManager = \Yii::$app->urlManager;

        // Pages rule globals
        $rule = new PageUrlRule();
        $route = 'pages/default/page';

        /**
         * Check url with params
         *  - pageId
         */
        $params = ['pageId' => 1];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = '-1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url with params
         *  - pageId
         *  - pageSlug
         */
        $params = ['pageId' => 1, 'pageSlug' => 'slug'];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = 'slug-1';

        $this->assertEquals($expectedUrl, $createdUrl);

        /**
         * Check url with params
         *  - pageId
         *  - pageSlug
         *  - pagePath
         */
        $params = ['pageId' => 1, 'pageSlug' => 'slug', 'pagePath' => 'subpage/next-subpage/next-subpage'];

        $createdUrl = $rule->createUrl($urlManager, $route, $params);
        $expectedUrl = 'subpage/next-subpage/next-subpage/slug-1';

        $this->assertEquals($expectedUrl, $createdUrl);
    }
}
