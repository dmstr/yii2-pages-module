<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the tree model class, extended from \kartik\tree\models\Tree
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
 * @property integer $access_owner
 * @property string $access_domain
 * @property string $access_read
 * @property string $access_update
 * @property string $access_delete
 * @property string $created_at
 * @property string $updated_at
 *
 */
class Tree extends \kartik\tree\models\Tree
{
    /**
     * Constants useful for frontend actions
     */
    const ICON_TYPE_CSS = 1;
    const ICON_TYPE_RAW = 2;

    const ACTIVE = 1;
    const NOT_ACTIVE = 0;

    const SELECTED = 1;
    const NOT_SELECTED = 0;

    const DISABLED = 1;
    const NOT_DISABLED = 0;

    const READ_ONLY = 1;
    const NOT_READ_ONLY = 0;

    const VISIBLE = 1;
    const NOT_VISIBLE = 0;

    const COLLAPSED = 1;
    const NOT_COLLAPSED = 0;

    /**
     * The root node domain_id prefix and level identifier
     */
    const ROOT_NODE_PREFIX = 'root';
    const ROOT_NODE_LVL = 0;

    /**
     * Attribute names
     */
    const ATTR_ID = 'id';
    const ATTR_NAME = 'name';
    const ATTR_DOMAIN_ID = 'domain_id';
    const ATTR_ACCESS_DOMAIN = 'access_domain';
    const ATTR_ROOT = 'root';
    const ATTR_ROUTE = 'route';
    const ATTR_VIEW = 'view';
    const ATTR_REQUEST_PARAMS = 'request_params';
    const ATTR_ICON = 'icon';
    const ATTR_ICON_TYPE = 'icon_type';
    const ATTR_ACTIVE = 'active';
    const ATTR_SELECTED = 'selected';
    const ATTR_DISABLED = 'disabled';
    const ATTR_READ_ONLY = 'readonly';
    const ATTR_VISIBLE = 'visible';
    const ATTR_COLLAPSED = 'collapsed';

    /**
     * Virtual attribute generated from "domain_id"_"access_domain"
     * @var $string
     */
    public $name_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmstr_page';
    }

    /**
     * @inheritdoc
     *
     * Use yii\behaviors\TimestampBehavior for created_at and updated_at attribute
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                [
                    'class'              => TimestampBehavior::className(),
                    'createdAtAttribute' => 'created_at',
                    'updatedAtAttribute' => 'updated_at',
                    'value'              => new Expression('NOW()'),
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [
                    ['domain_id', 'access_domain'],
                    'unique',
                    'targetAttribute' => ['domain_id', 'access_domain'],
                    'message'         => \Yii::t('app', 'Combination domain_id and access_domain must be unique!')
                ],
                [
                    [
                        'domain_id',
                    ],
                    'validateNoSpecialChars'
                ],
                [
                    [
                        'name',
                        'domain_id',
                    ],
                    'required'
                ],
                [
                    [
                        'domain_id',
                        'page_title',
                        'slug',
                        'route',
                        'view',
                        'default_meta_keywords',
                        'request_params',
                        'access_read',
                        'access_update',
                        'access_delete',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'default_meta_description',
                    ],
                    'string',
                    'max' => 160
                ],
                [
                    [
                        'access_domain',
                    ],
                    'string',
                    'max' => 8
                ],
                [
                    [
                        'access_domain',
                    ],
                    'default',
                    'value' => mb_strtolower(\Yii::$app->language)
                ],
                [
                    [
                        'root',
                        'access_owner',
                    ],
                    'integer',
                    'integerOnly' => true
                ],
                [
                    [
                        'domain_id',
                        'page_title',
                        'slug',
                        'route',
                        'view',
                        'default_meta_keywords',
                        'default_meta_description',
                        'request_params',
                        'access_domain',
                        'access_owner',
                        'access_read',
                        'access_update',
                        'access_delete',
                        'created_at',
                        'updated_at',
                    ],
                    'safe'
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
     * @param $attribute
     * @param $params
     */
    public function validateNoSpecialChars($attribute, $params)
    {
        // Check for whitespaces
        if (preg_match("/^[a-z0-9_\\/\\'\"]+$/", $this->domain_id) == 0) {
            $this->addError(
                $attribute,
                \Yii::t('app', '{0} should not contain any uppercase and special chars!', [$attribute])
            );
        }
    }

    /**^
     * Override isDisabled method if you need as shown in the
     * example below. You can override similarly other methods
     * like isActive, isMovable etc.
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
        $availableLanguages[mb_strtolower(Yii::$app->language)] = Yii::$app->language;
        return $availableLanguages;
    }

    /**
     * Get all configured
     * @return array list of options
     */
    public static function optsView()
    {
        return \Yii::$app->getModule('pages')->availableViews;
    }

    /**
     * TODO which routes will be provided by default ?
     *
     * @return array
     */
    public static function optsRoute()
    {
        return \Yii::$app->getModule('pages')->availableRoutes;
    }

    /**
     * @param array $additionalParams
     *
     * @return null|string
     */
    public function createUrl($additionalParams = [])
    {
        $route = [
            '/' . $this->route,
            'id'          => $this->id,
            'pageName'    => ($this->name)
                ? Inflector::slug($this->name)
                : '',
            'parentLeave' => ($this->parents(1)->one() && !$this->parents(1)->one()->isRoot())
                ? Inflector::slug($this->parents(1)->one()->name)
                : null,
        ];

        if (Json::decode($this->request_params)) {
            $route = ArrayHelper::merge($route, Json::decode($this->request_params));
        }

        if (!empty($additionalParams)) {
            $route = ArrayHelper::merge($route, $additionalParams);
        }

        return Url::toRoute($route);
    }

    /**
     * @return active and visible menu nodes for the current application language
     *
     * @param $domainId the domain id of the root node
     *
     * @return array
     */
    public static function getMenuItems($domainId, $checkUserPermissions = false)
    {
        // Get root node by domain id
        $rootCondition['domain_id']     = $domainId;
        $rootCondition['access_domain'] = mb_strtolower(\Yii::$app->language);
        if (!Yii::$app->user->can('pages')) {
            $rootCondition[Tree::ATTR_DISABLED] = Tree::NOT_DISABLED;
        }
        $rootNode = self::findOne($rootCondition);

        if ($rootNode === null) {
            return [];
        }

        /**
         * @var $leaves Tree[]
         */

        // Get all leaves from this root node
        $leavesQuery = $rootNode->children()->andWhere(
            [
                Tree::ATTR_ACTIVE        => Tree::ACTIVE,
                Tree::ATTR_VISIBLE       => Tree::VISIBLE,
                Tree::ATTR_ACCESS_DOMAIN => \Yii::$app->language,
            ]
        );
        if (!Yii::$app->user->can('pages')) {
            $leavesQuery->andWhere(
                [
                    Tree::ATTR_DISABLED => Tree::NOT_DISABLED,
                ]
            );
        }

        $leaves = $leavesQuery->all();

        if ($leaves === null) {
            return [];
        }

        // tree mapping and leave stack
        $treeMap = [];
        $stack   = [];

        if (count($leaves) > 0) {

            foreach ($leaves as $page) {

                // prepare node identifiers
                $pageOptions = [
                    'data-page-id' => $page->id,
                    'data-lvl'     => $page->lvl,
                ];

                $itemTemplate = [
                    'label'       => ($page->icon) ? '<i class="' . $page->icon . '"></i> ' . $page->name : $page->name,
                    'url'         => $page->createUrl(),
                    'linkOptions' => $pageOptions,
                    'visible'     => ($checkUserPermissions) ?
                        Yii::$app->user->can(substr(str_replace('/', '_', $page->route), 1), ['route' => true]) :
                        true,
                ];
                $item         = $itemTemplate;

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['linkOptions']['data-lvl'] >= $item['linkOptions']['data-lvl']) {
                    array_pop($stack);
                    $counter--;
                }

                // Stack is now empty (check root again)
                if ($counter == 0) {
                    // assign root node
                    $i           = count($treeMap);
                    $treeMap[$i] = $item;
                    $stack[]     = &$treeMap[$i];
                } else {
                    if (!isset($stack[$counter - 1]['items'])) {
                        $stack[$counter - 1]['items'] = [];
                    }
                    // add the node to parent node
                    $i                                = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[]                          = &$stack[$counter - 1]['items'][$i];
                }
            }
        }
        return array_filter($treeMap);
    }

    /**
     * Get virtual name_id
     * @return string
     */
    public function getNameId()
    {
        return $this->name_id;
    }

    /**
     * Generate and Set virtual attribute name_id
     *
     * @param mixed $name_id
     */
    public function setNameId($name_id)
    {
        $this->name_id = $name_id;
    }
}
