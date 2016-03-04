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
        $tree = Tree::getMenuItems('root_en');
        Debug::debug($tree);
    }

}