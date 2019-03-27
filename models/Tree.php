<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use dmstr\modules\pages\helpers\PageHelper;
use dmstr\modules\pages\Module as PagesModule;
use dosamigos\translateable\TranslateableBehavior;
use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use JsonSchema\Validator;

/**
 * Class Tree
 *
 * This is the tree model class, extended from \kartik\tree\models\Tree.
 *
 * @property string $name
 * @property string $name_id
 * @property string $domain_id
 * @property string $route
 * @property string $request_params
 * @property int $access_owner
 * @property string $access_domain
 * @property string $access_read
 * @property string $access_update
 * @property string $access_delete
 * @property string $created_at
 * @property string $updated_at
 *
 * @property string $menuLabel
 * @property string|mixed $nameId
 * @property bool $isDeletable
 * @property string requestParamsSchema
 *
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Tree extends BaseTree
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [
                    self::ATTR_REQUEST_PARAMS,
                    function ($attribute) {

                        $validator = new Validator();

                        $obj = Json::decode($this->requestParamsSchema, false);
                        $data = Json::decode($this->{$attribute}, false);
                        $validator->check($data, $obj);
                        if ($validator->getErrors()) {
                            foreach ($validator->getErrors() as $error) {
                                $this->addError($error['property'], "{$error['property']}: {$error['message']}");
                            }
                        }

                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->setNameId($this->domain_id . '_' . $this->access_domain);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(\Yii::$app->cache, 'pages');
    }


    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if (!$this->isDeletable) {
            // send message to user so he knows whats going on
            Yii::$app->session->addFlash('info', Yii::t('pages', 'You can not delete this record. There is still a translation that uses this entry as a reference.'));
        }

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        TagDependency::invalidate(\Yii::$app->cache, 'pages');
    }

    /**
     * Disallow node movement when user has no update permissions
     *
     * @param string $dir
     *
     * @return bool
     */
    public function isMovable($dir)
    {
        if (!$this->hasPermission('access_update')) {
            return false;
        }

        return parent::isMovable($dir);
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
     *
     * @return array
     */
    public static function optsSourceRootId()
    {
        // disable access trait to find root nodes in all languages
        self::$activeAccessTrait = false;

        // find all root nodes but global access domain nodes
        $rootNodes = self::find()
            ->where([self::ATTR_LVL => self::ROOT_NODE_LVL])
            ->andWhere(['NOT', [self::ATTR_ACCESS_DOMAIN => self::GLOBAL_ACCESS_DOMAIN]])
            ->all();

        if (empty($rootNodes)) {
            return [];
        }

        return ArrayHelper::map($rootNodes, 'id', 'name_id');
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
     *
     * @param bool $html whether to render icon as array value prefix
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function optsIcon($html = false)
    {
        $result = [];
        foreach ((new \ReflectionClass(FA::class))->getConstants() as $constant) {
            $key = $constant;

            $result[$key] = $html
                ? FA::icon($constant) . '&nbsp;&nbsp;' . $constant
                : $constant;
        }
        return $result;
    }

    /**
     * @param array $additionalParams
     *
     * @return array|string|null
     */
    public function createRoute($additionalParams = [])
    {
        if (!$this->route) {
            return null;
        }

        $route = [
            $this->route,
            'slugged_menu_name' => Inflector::slug($this->name)
        ];

        $resolved_path = Inflector::slug($this->resolvePagePath(true));

        if (!empty($resolved_path)) {
            $route['slugged_menu_path'] = $resolved_path;
        }

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
     * @param array $linkOptions
     *
     * @return array
     */
    public static function getMenuItems($domainId, $checkUserPermissions = false, array $linkOptions = [])
    {
        $cache = Yii::$app->cache;
        $cacheKey = Json::encode([self::class, Yii::$app->language, $domainId, $checkUserPermissions, $linkOptions]);
        $data = $cache->get($cacheKey);

        if ($data !== false && Yii::$app->user->isGuest) {
            return $data;
        }

        Yii::debug(['Building menu items', $cacheKey], __METHOD__);
        // Get root node by domain id
        $rootCondition[self::ATTR_DOMAIN_ID] = $domainId;
        $rootCondition[self::ATTR_ACCESS_DOMAIN] = [self::GLOBAL_ACCESS_DOMAIN, mb_strtolower(\Yii::$app->language)];
        $rootNode = self::findOne($rootCondition);

        if ($rootNode === null) {
            return [];
        }

        if ($rootNode->isDisabled() && !Yii::$app->user->can(self::PAGES_ACCESS_PERMISSION)) {
            return [];
        }

        /**
         * @var $leaves Tree[]
         */

        // Get all leaves from this root node
        $leavesQuery = $rootNode->children()->andWhere(
            [
                self::ATTR_ACTIVE => self::ACTIVE,
                self::ATTR_ACCESS_DOMAIN => [self::GLOBAL_ACCESS_DOMAIN, mb_strtolower(\Yii::$app->language)],
            ]
        );
        $leavesQuery->with('translationsMeta');
        $leaves = $leavesQuery->all();

        if ($leaves === null) {
            return [];
        }

        // filter out invisible models and disabled models (if needed)
        // this is not done in the SQL query to reflect translation_meta values for "visible" and "disabled" attributes.
        $canAccessPages = Yii::$app->user->can(self::PAGES_ACCESS_PERMISSION);
        $leaves = array_filter($leaves, function (Tree $leave) use ($canAccessPages) {
            if (!$leave->isVisible()) {
                return false;
            }
            if (!$canAccessPages && $leave->isDisabled()) {
                return false;
            }
            return true;
        });


        // tree mapping and leave stack
        $treeMap = [];
        $stack = [];

        if (count($leaves) > 0) {
            foreach ($leaves as $page) {
                /** @var Tree $page */

                // prepare node identifiers
                $linkOptions = ArrayHelper::merge(
                    $linkOptions,
                    [
                        'data-page-id' => $page->id,
                        'data-domain-id' => $page->domain_id,
                        'data-lvl' => $page->lvl,
                        'class' => $page->isDisabled() ? 'dmstr-pages-invisible-frontend' : ''
                    ]
                );

                $visible = true;
                if ($checkUserPermissions) {
                    if ($page->access_read !== '*') {
                        \Yii::debug('Checking Access_read permissions for page ' . $page->id, __METHOD__);
                        $visible = Yii::$app->user->can($page->access_read);
                    } else if (!empty($page->route)) {
                        $visible = Yii::$app->user->can(substr(str_replace('/', '_', $page->route), 1), ['route' => true]);
                    }
                }


                // prepare item template
                $itemTemplate = [
                    'label' => $page->getMenuLabel(),
                    'url' => $page->createRoute() ?: null,
                    'icon' => $page->icon,
                    'linkOptions' => $linkOptions,
                    'dropDownOptions' => [
                        'data-parent-domain-id' => $page->domain_id,
                    ],
                    'visible' => $visible,
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

        $data = array_filter($treeMap);

        if (Yii::$app->user->isGuest) {
            $cacheDependency = new TagDependency(['tags' => 'pages']);
            $cache->set($cacheKey, $data, 3600, $cacheDependency);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getMenuLabel()
    {
        return !empty($this->name) ? htmlentities($this->name) : "({$this->domain_id})";
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
    protected function resolvePagePath($activeNode = false)
    {

        // get TreeCache singleton instance as cache
        $cache = TreeCache::getInstance();

        // define cache key fro model id + app->lang
        $cacheKey = $this->id . Yii::$app->language;

        // if set, return path from cache
        if (isset($cache->path[$cacheKey])) {
            return $cache->path[$cacheKey];
        }

        Yii::beginProfile('Resolving page path', __METHOD__);

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
            $path = Inflector::slug($this->name);
        } else if (!$activeNode) {
            // if not active, build up path
            $path = $parent->resolvePagePath() . '/' . Inflector::slug($this->name);
        } else if ($activeNode && !$parent->isRoot()) {
            // building path finished
            $path = $parent->resolvePagePath();
        } else {
            $path = null;
        }

        // store path in cache
        $cache->path[$cacheKey] = $path;
        Yii::endProfile('Resolving page path', __METHOD__);

        return $path;
    }

    /**
     * Conditions for a full page object
     *
     * @return bool
     */
    public function isPage()
    {
        switch (true) {
            case $this->isRoot():
            case $this->isLeaf():
            case $this->isNewRecord:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Find the sibling page in target language if exists
     *
     * @param string $targetLanguage
     * @param integer $sourceId
     *
     * @return Tree|null
     * @throws \yii\console\Exception
     */
    public function sibling($targetLanguage, $sourceId = null)
    {


        // Disable access trait access_domain checks in find
        self::$activeAccessTrait = false;

        if ($sourceId === null && !$this->isNewRecord) {
            $sourcePage = $this;
        } else {
            /**
             * find page with page id and source language
             *
             * @var Tree $sourcePage
             */
            $sourcePage = self::findOne($sourceId);
            if ($sourcePage === null) {
                $message = \Yii::t(
                    'pages',
                    'Page with id {PAGE_ID} not found!"',
                    ['PAGE_ID' => $sourceId]
                );
                $errorCode = 404;
                $this->outputError($message, $errorCode);
            }
        }

        /**
         * find page with domain_id and destination language
         *
         * @var Tree $destinationPage
         */
        $destinationPage = self::findOne(
            [
                self::ATTR_DOMAIN_ID => $sourcePage->domain_id,
                self::ATTR_ACCESS_DOMAIN => mb_strtolower($targetLanguage)
            ]
        );
        if ($destinationPage === null) {
            $message = \Yii::t(
                'pages',
                'Page with domain_id {DOMAIN_ID} in language "{LANGUAGE}" does not exists!',
                [
                    'DOMAIN_ID' => $sourcePage->domain_id,
                    'LANGUAGE' => mb_strtolower($targetLanguage)
                ]
            );
            $errorCode = 404;
            $this->outputError($message, $errorCode);
        }
        return $destinationPage;
    }

    /**
     * @param $message
     * @param $code
     *
     * @return bool
     * @throws \yii\console\Exception
     */
    protected function outputError($message, $code)
    {
        if (PHP_SAPI === 'cli') {
            throw new \yii\console\Exception($message, $code);
        }

        \Yii::$app->session->set('error', $code . ': ' . $message);
        return false;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getRequestParamsSchema()
    {
        return PageHelper::routeToSchema($this->route);
    }


    /**
     * Checks if model can be deleted in case it has only one (or less) translation left
     *
     * @return bool
     */
    public function getIsDeletable()
    {
        /** @var TranslateableBehavior $translatableBehavior */
        $translatableBehavior = $this->getBehavior('translatable');

        if ($translatableBehavior->restrictDeletion === TranslateableBehavior::DELETE_LAST) {
            return (int)$this->getTranslations()->count() <= 1;
        }

        return true;
    }
}
