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
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the tree model class, extended from \kartik\tree\models\Tree
 *
 * @property string  $page_title
 * @property string  $name_id
 * @property string  $slug
 * @property string  $route
 * @property string  $view
 * @property string  $default_meta_keywords
 * @property string  $default_meta_description
 * @property string  $request_params
 * @property integer $access_owner
 * @property string  $access_domain
 * @property string  $access_read
 * @property string  $access_update
 * @property string  $access_delete
 * @property string  $created_at
 * @property string  $updated_at
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
     * Attribute names
     */
    const ATTR_ID = 'id';
    const ATTR_NAME_ID = 'name_id';
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
                    [
                        'name_id',
                    ],
                    'unique'
                ],
                [
                    [
                        'name_id',
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
                        'access_domain',
                    ],
                    'string',
                    'max' => 8
                ],
                [
                    [
                        'access_owner',
                    ],
                    'integer',
                    'max' => 11
                ],
                [
                    [
                        'name_id',
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
     * Override isDisabled method if you need as shown in the
     * example below. You can override similarly other methods
     * like isActive, isMovable etc.
     */
    public function isDisabled()
    {
        //if (Yii::$app->user->id !== 'admin') {
        //return true;
        //}

        return parent::isDisabled();
    }

    /**
     * Get all configured
     * @return array list of options
     */
    public static function optsView()
    {
        return \Yii::$app->getModule('pages')->params['availableViews'];
    }

    /**
     * @param array $additionalParams
     *
     * @return null|string
     */
    public function createUrl($additionalParams = [])
    {
        $leave = Tree::find()->where(['id' => $this->id])->one();

        if ($leave === null) {
            Yii::error("Tree node with id=" . $this->id . " not found.");
            return null;
        }

        if ($leave->route !== null && $leave->request_params !== null) {

            if ($additionalParams) {
                // merge with $params
            }


            // TODO provide all parents in URL
            // provide first parent for URL creation
            $parent      = $leave->parents(1)->one();
            $parentLeave = null;

            if ($parent) {
                if ($parent->lvl != '0') {
                    $parentLeave = Inflector::slug($parent->name);
                }
            }

            // TODO merged request and additional params, URL rule has therefore to be updated/extended
            return Url::toRoute(
                [
                    $leave->route,
                    'id'          => $leave->id,
                    'pageName'    => Inflector::slug($leave->page_title),
                    'parentLeave' => $parentLeave
                ]
            );
        } elseif ($leave->route !== null) {
            return \Yii::$app->urlManager->createUrl([$leave->route]);
        }
    }

    /**
     * @param $rootName the name of the root node
     *
     * @return array
     */
    public static function getMenuItems($rootName)
    {
        // Get root node by name
        $rootNode = self::findOne(['name_id' => $rootName]);

        if ($rootNode === null) {
            return [];
        }

        // Get all leaves from this root node
        $leaves = $rootNode->children()->all();

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
}