<?php

namespace dmstr\modules\pages\tests\unit;

use dmstr\modules\pages\models\Tree;

class ModelTestCase extends \Codeception\Test\Unit
{
    // tests
    public function testRootNode()
    {
        \Yii::$app->language = 'de';

        $root = Tree::findOne(['domain_id' => 'root']);

        if (empty($root)) {

            $root = new Tree();
            $root->name = 'Willkommen';
            $root->domain_id = 'root';

            // treemanager settings
            $root->purifyNodeIcons = false;
            $root->encodeNodeNames = false;

            $root->makeRoot();
        }

        $this->assertSame($root->domain_id, 'root', 'Root node has errors');
    }

    public function testMenuItems()
    {
        \Yii::$app->language = 'de';

        $root = Tree::findOne(['domain_id' => 'root']);

        /**
         * Insert a leave and append to root node
         */
        $leave = new Tree();
        $leave->name = 'Seite 1';

        // treemanager settings
        $leave->purifyNodeIcons = false;
        $leave->encodeNodeNames = false;

        $leave->appendTo($root);

        /**
         * Insert another leave and append to root node
         */
        $leave = new Tree();
        $leave->name = 'Seite 1';

        // treemanager settings
        $leave->purifyNodeIcons = false;
        $leave->encodeNodeNames = false;

        $leave->appendTo($root);

        // get root node menu items
        $tree = Tree::getMenuItems('root');

        $this->assertNotNull(count($tree), 'Root node not found');
    }

    /**
     * Test the virtual name_id attribute setter and getter for 'de' and 'en' root pages
     * @return mixed
     */
    public function testNameId()
    {
        $pages = Tree::findAll(
            [
                Tree::ATTR_DOMAIN_ID => 'root',
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
                Tree::ATTR_VISIBLE => Tree::VISIBLE,
            ]
        );
        if ($pages) {
            foreach ($pages as $page) {
                $buildNameId = $page->domain_id.'_'.$page->access_domain;
                $this->assertSame($buildNameId, $page->name_id, 'NameID was not set properly');
            }
        } else {
            $this->assertNotEmpty($pages, 'No Pages found!');
        }
    }

    /**
     * remove a root node
     */
    public function testRemoveRootNode()
    {
        $root = Tree::findOne(['domain_id' => 'root']);
        $root->purifyNodeIcons = false;
        $root->encodeNodeNames = false;

        if ($root->isRemovable()) {
            $root->deleteWithChildren();
        } else {
            $this->assertFalse($root->attributes, 'Root node can not be deleted');
        }
        $root = Tree::findOne(['domain_id' => 'root']);
        $this->assertNull($root);
    }
}
