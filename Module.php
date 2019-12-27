<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages;

use dmstr\modules\pages\models\Tree;
use dmstr\web\traits\AccessBehaviorTrait;
use yii\console\Application;

/**
 * Class Module.
 *
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 *
 * @property mixed|object $localizedRootNode
 */
class Module extends \yii\base\Module
{
    use AccessBehaviorTrait;

    /**
     * The name of this module
     */
    const NAME = 'pages';

    /**
     * @var array the list of rights that are allowed to access this module.
     *            If you modify, you also need to enable authManager.
     *            http://www.yiiframework.com/doc-2.0/guide-security-authorization.html
     */
    public $roles = [];

    /**
     * alias for the pages/default/page action
     *
     * @var string
     */
    public $defaultPageLayout = '@app/views/layouts/main';

    /**
     * @var array
     */
    public $availableRoutes = [];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // add routes from settings module
        if ($this->checkSettingsInstalled()) {
            $routes = explode("\n", \Yii::$app->settings->get('pages.availableRoutes'));
            foreach ($routes as $route) {
                $routeEntry = trim($route);
                $this->availableRoutes[$routeEntry] = $routeEntry;
            }



            if (!\Yii::$app instanceof Application && \Yii::$app->has('user') && \Yii::$app->user->can(Tree::GLOBAL_ACCESS_PERMISSION)) {
                $globalRoutes = explode("\n", \Yii::$app->settings->get('pages.availableGlobalRoutes'));
                foreach ($globalRoutes as $globalRoute) {
                    $globalRouteEntry = trim($globalRoute);
                    $this->availableRoutes[$globalRouteEntry] = $globalRouteEntry;
                }
            }
        }
    }

    /**
     * @return mixed|object dmstr\modules\pages\models\Tree
     */
    public function getLocalizedRootNode()
    {
        $localizedRoot = Tree::ROOT_NODE_PREFIX.'_'.\Yii::$app->language;
        \Yii::debug('localizedRoot: '.$localizedRoot, __METHOD__);
        $rootNode = Tree::findOne(
            [
                Tree::ATTR_DOMAIN_ID => Tree::ROOT_NODE_PREFIX,
                Tree::ATTR_ACTIVE => Tree::ACTIVE,
            ]
        );
        if ($rootNode !== null && !$rootNode->isVisible()) {
            return null;
        }
        return $rootNode;
    }

    /**
     * Check for "pheme/yii2-settings" component and module
     * @return bool
     */
    private function checkSettingsInstalled()
    {
        return \Yii::$app->hasModule('settings') && \Yii::$app->has('settings');
    }
}
