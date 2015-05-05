<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages;

use dmstr\modules\pages\models\Tree;

/**
 * Class Module
 * @package dmstr\modules\pages
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Module extends \yii\base\Module
{
    public function run()
    {

    }

    public function getLocalizedRootNode()
    {
        $localizedRoot = 'root_' . \Yii::$app->language;
        \Yii::trace('localizedRoot: ' . $localizedRoot, __METHOD__);
        $page = Tree::findOne(
            [
                Tree::ATTR_NAME_ID => $localizedRoot,
                Tree::ATTR_ACTIVE  => Tree::ACTIVE,
                Tree::ATTR_VISIBLE => Tree::VISIBLE
            ]
        );
        return $page;
    }
}
