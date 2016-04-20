<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages\controllers;

use dmstr\modules\pages\models\Tree;
use yii\web\Controller;

/**
 * Class TestController.
 *
 * @author $Author
 */
class TestController extends Controller
{
    public function actionIndex()
    {
        $tree = Tree::getMenuItems(Tree::ROOT_NODE_PREFIX);

        return $this->render('index', ['tree' => $tree]);
    }
}
