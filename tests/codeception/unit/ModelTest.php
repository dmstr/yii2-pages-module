<?php
// @group mandatory

namespace dmstr\modules\pages\tests\unit;

use dmstr\modules\pages\models\Tree;

class ModelTestCase extends \Codeception\Test\Unit
{
    // tests
    public function testRootNode()
    {
        \Yii::$app->language = 'de';

        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );

        if (empty($root)) {
            $root = $this->createRootNode('de');
        }

        $this->assertSame($root->domain_id, 'root', 'Root node has errors');
    }

    /**
     * - Add menu items to root node
     * - Check domain id will be automatically generated if not set
     */
    public function testAddMenuItems()
    {
        \Yii::$app->language = 'de';

        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );

        $this->assertNotNull($root, 'Root node not found');

        /**
         * Insert a leave and append to root node
         */
        $leave       = new Tree();
        $leave->name = 'Seite 1';

        // treemanager settings
        $leave->purifyNodeIcons = false;
        $leave->encodeNodeNames = false;

        $leave->appendTo($root);

        /**
         * Insert another leave and append to root node
         */
        $leave       = new Tree();
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
    public function testvalidateNameIdGeneration()
    {
        $pages = Tree::findAll(
            [
                Tree::ATTR_DOMAIN_ID => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACTIVE    => Tree::ACTIVE,
                Tree::ATTR_VISIBLE   => Tree::VISIBLE,
            ]
        );
        if ($pages !== null) {
            foreach ($pages as $page) {
                $buildNameId = $page->domain_id . '_' . $page->access_domain;
                $this->assertSame($buildNameId, $page->name_id, 'NameID was not set properly');
            }
        } else {
            $this->assertNotEmpty($pages, 'No Pages found!');
        }
    }

    /**
     * remove a root node
     */
    public function testRemoveRootNodeWithChildren()
    {
        \Yii::$app->language = 'de';

        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );

        $this->assertNotNull($root, 'Root node not found');

        $root->purifyNodeIcons = false;
        $root->encodeNodeNames = false;

        if ($root->isRemovable()) {
            $root->deleteWithChildren();
            $this->assertNotEmpty($root->attributes, 'Root node deleted');
        } else {
            $this->assertFalse($root->attributes, 'Root node can not be deleted');
        }
        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );
        $this->assertNull($root);
    }

    /**
     * Test find records only for current access domain, feature from
     * \dmstr\db\traits\ActiveRecordAccessTrait
     */
    public function testAccessDomainCheckOnFind()
    {
        // ensure a 'de' root node exists
        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );

        // switch to app language 'en'
        \Yii::$app->language = 'en';

        // try to find the 'de' root not in from app language 'en'
        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );
        // expect false
        $this->assertNull($root, 'Root node "de" found from app language "en"');

        // switch to app language 'de'
        \Yii::$app->language = 'de';

        // try to find the 'de' root from app language 'de'
        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );

        // expect true
        $this->assertNotNull($root, 'Root node "de" found from app language "de"');
    }

    /**
     * Test update an attribute of a tree node
     */
    public function testUpdatePageNode()
    {
        // switch to app language 'de'
        \Yii::$app->language = 'de';

        // try to find the 'de' root from app language 'de'
        $root = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID     => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => 'de',
            ]
        );
        // expect true
        $this->assertNotNull($root, 'Root node "de" found from app language "de"');

        /** @var Tree $root */
        $root->purifyNodeIcons = false;
        $root->encodeNodeNames = false;
        $root->page_title = "Updated Page Title";
        $root->save();

        // expect true
        $this->assertSame($root->page_title, 'Updated Page Title');
    }

    /**
     * create empty root node fo a language
     * @param $language
     *
     * @return Tree
     */
    private function createRootNode($language)
    {
        $root                = new Tree();
        $root->name          = 'Willkommen';
        $root->domain_id     = 'root';
        $root->access_domain = $language;

        // treemanager settings
        $root->purifyNodeIcons = false;
        $root->encodeNodeNames = false;

        $root->makeRoot();

        return $root;
    }
}
