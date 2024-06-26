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
     * @var array
     */
    public $availableViews = [];

    /**
     * Whether access_domain should be used as constraint in default/page action select
     *
     * @var bool
     */
    public $pageCheckAccessDomain = false;

    /**
     * Whether to search fallbackPage according to domain_id
     *
     * see: \dmstr\modules\pages\controllers\DefaultController::resolveFallbackPage
     * @var bool
     */
    public $pageUseFallbackPage = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // add routes from settings module
        if (self::checkSettingsInstalled()) {
            $routes = explode("\n", (string)\Yii::$app->settings->get('pages.availableRoutes'));
            foreach ($routes as $route) {
                $routeEntry = trim($route);
                $this->availableRoutes[$routeEntry] = $routeEntry;
            }

            $views = explode("\n", (string)\Yii::$app->settings->get('pages.availableViews'));
            foreach ($views as $view) {
                // use custom name if appended after a semicolon (;)
                $viewEntry = explode(';', trim($view));
                $this->availableViews[$viewEntry[0]] = $viewEntry[1] ?? $viewEntry[0];
            }

            if (!\Yii::$app instanceof Application && \Yii::$app->has('user') && \Yii::$app->user->can(Tree::GLOBAL_ACCESS_PERMISSION)) {
                $globalRoutes = explode("\n", (string)\Yii::$app->settings->get('pages.availableGlobalRoutes'));
                foreach ($globalRoutes as $globalRoute) {
                    $globalRouteEntry = trim($globalRoute);
                    $this->availableRoutes[$globalRouteEntry] = $globalRouteEntry;
                }

                $globalViews = explode("\n", (string)\Yii::$app->settings->get('pages.availableGlobalViews'));
                foreach ($globalViews as $globalView) {
                    // use custom name if appended after a semicolon (;)
                    $globalViewEntry = explode(';', trim($globalView));
                    $this->availableViews[$globalViewEntry[0]] = $globalViewEntry[1] ?? $globalViewEntry[0];
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
        \Yii::trace('localizedRoot: '.$localizedRoot, __METHOD__);
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
    public static function checkSettingsInstalled()
    {
        return \Yii::$app->hasModule('settings') && \Yii::$app->has('settings');
    }
}
