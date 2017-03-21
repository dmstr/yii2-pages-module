<?php
namespace dmstr\modules\pages\models;
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use rmrevin\yii\fontawesome\FA;
use Yii;
use dmstr\modules\pages\Module as PagesModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Tree
 *
 * This is the tree model class, extended from \kartik\tree\models\Tree.
 *
 * @property string $name
 * @property string $page_title
 * @property string $name_id
 * @property string $domain_id
 * @property string $slug
 * @property string $route
 * @property string $view
 * @property string $default_meta_keywords
 * @property string $default_meta_description
 * @property string $request_params
 * @property int $access_owner
 * @property string $access_domain
 * @property string $access_read
 * @property string $access_update
 * @property string $access_delete
 * @property string $created_at
 * @property string $updated_at
 *
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Tree extends BaseTree
{
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->setNameId($this->domain_id.'_'.$this->access_domain);
    }

    /**
     * Override isDisabled method if you need as shown in the
     * example below. You can override similarly other methods
     * like isActive, isMovable etc.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return parent::isDisabled();
    }

    /**
     * @return array
     */
    public static function optsAccessDomain()
    {
        $currentLanguage = mb_strtolower(Yii::$app->language);
        $availableLanguages[$currentLanguage] = $currentLanguage;

        if (Yii::$app->user->can(self::GLOBAL_ACCESS_PERMISSION)) {
            $availableLanguages[self::GLOBAL_ACCESS_DOMAIN] = Yii::t(self::PAGES_ACCESS_PERMISSION, 'GLOBAL');
        }

        return $availableLanguages;
    }

    /**
     * Renders all available root nodes as mapped array, `id` => `name_id`
     * @return array
     */
    public static function optsSourceRootId()
    {
        // disable access trait to find root nodes in all languages
        self::$activeAccessTrait = false;

        // find all root nodes but global access domain nodes
        $rootNodes = self::find()
            ->where([Tree::ATTR_LVL => Tree::ROOT_NODE_LVL])
            ->andWhere(['NOT', [Tree::ATTR_ACCESS_DOMAIN => Tree::GLOBAL_ACCESS_DOMAIN]])
            ->all();

        if (empty($rootNodes)) {
            return [];
        }

        return ArrayHelper::map($rootNodes, 'id', 'name_id');
    }

    /**
     * Get all configured views
     *
     * @return array list of options
     */
    public static function optsView()
    {
        return \Yii::$app->getModule(PagesModule::NAME)->availableViews;
    }

    /**
     * Get all configured routs
     *
     * @return array list of options
     */
    public static function optsRoute()
    {
        return \Yii::$app->getModule(PagesModule::NAME)->availableRoutes;
    }

    /**
     * Get all icon constants for dropdown list in example
     * @param bool $html whether to render icon as array value prefix
     * @return array
     */
    public static function optsIcon($html = false)
    {
        $result = [];
        foreach ((new \ReflectionClass(FA::class))->getConstants() as $constant) {
            $key = $constant;

            $result[$key] = ($html)
                ? FA::icon($constant) . '&nbsp;&nbsp;' . $constant
                : $constant;
        }
        return $result;
    }

    /**
     * @param array $additionalParams
     *
     * @return null|string
     */
    public function createRoute($additionalParams = [])
    {
        if (!$this->route) {
            return null;
        }

        $pageId = null;
        $slug = null;
        $slugFolder = null;

        // us this params only for the default page route
        if ($this->route === self::DEFAULT_PAGE_ROUTE) {
            $pageId = $this->id;
            $slug = ($this->page_title)
                ? Inflector::slug($this->page_title)
                : Inflector::slug($this->name);
            $slugFolder = $this->resolvePagePath(true);
        }

        $route = [
            $this->route,
            self::REQUEST_PARAM_ID => $pageId,
            self::REQUEST_PARAM_SLUG => $slug,
            self::REQUEST_PARAM_PATH => $slugFolder
        ];

        if (Json::decode($this->request_params)) {
            $route = ArrayHelper::merge($route, Json::decode($this->request_params));
        }

        if (!empty($additionalParams)) {
            $route = ArrayHelper::merge($route, $additionalParams);
        }

        return $route;
    }

    /**
     * @param array $additionalParams
     *
     * @return string
     */
    public function createUrl($additionalParams = [])
    {
        return Url::to($this->createRoute($additionalParams));
    }

    /**
     * Build array with active and visible menu nodes for the current application language
     *
     * @param string $domainId the domain id of the root node
     * @param bool|false $checkUserPermissions weather to check permissions for the node leave routes
     *
     * @return array
     */
    public static function getMenuItems($domainId, $checkUserPermissions = false)
    {
        // Get root node by domain id
        $rootCondition[self::ATTR_DOMAIN_ID] = $domainId;
        $rootCondition[self::ATTR_ACCESS_DOMAIN] = [self::GLOBAL_ACCESS_DOMAIN,mb_strtolower(\Yii::$app->language)];
        if (!Yii::$app->user->can(self::PAGES_ACCESS_PERMISSION)) {
            $rootCondition[self::ATTR_DISABLED] = self::NOT_DISABLED;
        }
        $rootNode = self::findOne($rootCondition);

        if ($rootNode === null) {
            return [];
        }

        /*
         * @var $leaves Tree[]
         */

        // Get all leaves from this root node
        $leavesQuery = $rootNode->children()->andWhere(
            [
                self::ATTR_ACTIVE => self::ACTIVE,
                self::ATTR_VISIBLE => self::VISIBLE,
                self::ATTR_ACCESS_DOMAIN => [self::GLOBAL_ACCESS_DOMAIN,mb_strtolower(\Yii::$app->language)],
            ]
        );
        if (!Yii::$app->user->can(self::PAGES_ACCESS_PERMISSION)) {
            $leavesQuery->andWhere(
                [
                    self::ATTR_DISABLED => self::NOT_DISABLED,
                ]
            );
        }

        $leaves = $leavesQuery->all();

        if ($leaves === null) {
            return [];
        }

        // tree mapping and leave stack
        $treeMap = [];
        $stack = [];

        if (count($leaves) > 0) {
            foreach ($leaves as $page) {
                /** @var Tree $page */

                // prepare node identifiers
                $pageOptions = [
                    'data-page-id' => $page->id,
                    'data-lvl' => $page->lvl,
                ];

                // prepare item template
                $itemTemplate = [
                    'label' => $page->name,
                    'url' => $page->createRoute(),
                    'icon' => $page->icon,
                    'linkOptions' => $pageOptions,
                    // always show node, if it's a folder (TODO add check permissions)
                    'visible' => ($checkUserPermissions && !empty($page->route)) ?
                        Yii::$app->user->can(substr(str_replace('/', '_', $page->route), 1), ['route' => true]) :
                        true,
                ];
                $item = $itemTemplate;

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['linkOptions']['data-lvl'] >= $item['linkOptions']['data-lvl']) {
                    array_pop($stack);
                    --$counter;
                }

                // Stack is now empty (check root again)
                if ($counter == 0) {
                    // assign root node
                    $i = count($treeMap);
                    $treeMap[$i] = $item;
                    $stack[] = &$treeMap[$i];
                } else {
                    if (!isset($stack[$counter - 1]['items'])) {
                        $stack[$counter - 1]['items'] = [];
                    }
                    // add the node to parent node
                    $i = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[] = &$stack[$counter - 1]['items'][$i];
                }
            }
        }

        return array_filter($treeMap);
    }

    /**
     * Get virtual name_id.
     *
     * @return string
     */
    public function getNameId()
    {
        return $this->name_id;
    }

    /**
     * Generate and Set virtual attribute name_id.
     *
     * @param mixed $name_id
     */
    public function setNameId($name_id)
    {
        $this->name_id = $name_id;
    }

    /**
     * @param bool|false $activeNode
     *
     * @return null|string
     */
    protected function resolvePagePath($activeNode = false){

        // return no path for root nodes
        $parent = $this->parents(1)->one();
        if (!$parent) {
            return null;
        }
        /** @var Tree $parent */

        // return no path for first level nodes
        if ($activeNode && $parent->isRoot()) {
            return null;
        }

        if (!$activeNode && $parent->isRoot()) {
            // start-point for building path
            $path = Inflector::slug(($this->page_title?:$this->name));
        } else if (!$activeNode) {
            // if not active, build up path
            $path = $parent->resolvePagePath(false).'/'.Inflector::slug(($this->page_title?:$this->name));
        } else if ($activeNode && !$parent->isRoot()) {
            // building path finished
            $path = $parent->resolvePagePath(false);
        } else {
            $path = null;
        }

        return $path;
    }

    /**
     * Conditions for a full page object
     *
     * @return bool
     */
    public function isPage()
    {
        switch(true) {
            case $this->isRoot():
            case $this->isLeaf():
            case $this->isNewRecord:
                return true;
                break;
            default:
                return false;
        }
    }
}
