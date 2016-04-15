<?php

namespace dmstr\modules\pages\tests\unit;

use Codeception\Util\Debug;
use dmstr\modules\pages\models\Tree;

class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '/app/vendor/dmstr/yii2-pages-module/tests/_config/unit.php';

    // tests
    public function testInit()
    {
        $this->assertFalse(false);
    }

    public function testMenuItems()
    {
        $tree = Tree::getMenuItems(Tree::ROOT_NODE_PREFIX);
        Debug::debug($tree);
    }

    /**
     * Test the virtual name_id attribute setter and getter for 'de' and 'en' root pages
     * @return mixed
     */
    public function testNameId()
    {
        $pages = Tree::findAll(
            [
                Tree::ATTR_DOMAIN_ID => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACTIVE    => Tree::ACTIVE,
                Tree::ATTR_VISIBLE   => Tree::VISIBLE,
            ]
        );
        if ($pages) {
            foreach ($pages as $page) {
                $buildNameId = $page->domain_id . '_' . $page->access_domain;
                $this->assertSame($buildNameId, $page->name_id, 'NameID was not set proberly');
            }
        } else {
            return $this->assertNotEmpty($pages, 'No Page not found!');
        }
    }

}