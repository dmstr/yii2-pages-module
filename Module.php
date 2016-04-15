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
use dmstr\web\traits\AccessBehaviorTrait;

/**
 * Class Module
 * @package dmstr\modules\pages
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Module extends \yii\base\Module
{
    use AccessBehaviorTrait;

    /**
     * @var array the list of rights that are allowed to access this module.
     * If you modify, you also need to enable authManager.
     * http://www.yiiframework.com/doc-2.0/guide-security-authorization.html
     */
    public $roles = [];

    public $pagesWithChildrenHasUrl = false;

    public $availableRoutes = [];
    
    public $availableViews = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // add routes from settings module
        if (\Yii::$app->hasModule('settings')) {
            $routes = explode("\n", \Yii::$app->settings->get('pages.availableRoutes'));
            foreach ($routes AS $route) {
                $this->availableRoutes[trim($route)] = trim($route);
            }

            $views = explode("\n", \Yii::$app->settings->get('pages.availableViews'));
            foreach ($views AS $view) {
                $this->availableViews[trim($view)] = trim($view);
            }
        }
    }

    /**
     * @return mixed|object dmstr\modules\pages\models\Tree
     */
    public function getLocalizedRootNode()
    {
        $localizedRoot = Tree::ROOT_NODE_PREFIX . '_' . \Yii::$app->language;
        \Yii::trace('localizedRoot: ' . $localizedRoot, __METHOD__);
        $page = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACCESS_DOMAIN => mb_strtolower(\Yii::$app->language),
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
                Tree::ATTR_VISIBLE => Tree::VISIBLE
            ]
        );
        return $page;
    }
}
