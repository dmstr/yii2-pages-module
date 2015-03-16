<?php

namespace dmstr\modules\pages\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\HttpException;

/**
 * This is the tree model class, extended from \kartik\tree\models\Tree
 *
 * @property string  $page_title
 * @property string  $name_id
 * @property string  $slug
 * @property string  $route
 * @property string  $default_meta_keywords
 * @property string  $default_meta_description
 * @property string  $request_params
 * @property integer $owner
 * @property string  $created_at
 * @property string  $updated_at
 *
 */
class Tree extends \kartik\tree\models\Tree
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmstr_pages';
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
                    'required'
                ],
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
                        'default_meta_keywords',
                        'default_meta_description',
                        'request_params',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'owner',
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
                        'default_meta_keywords',
                        'default_meta_description',
                        'request_params',
                        'owner',
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
            $params = Json::decode($leave->request_params);
            return \Yii::$app->urlManager->createUrl(
                [
                    $leave->route . '?page_name=' . $params['page_name'],
                    /*ArrayHelper::merge(
                        $additionalParams,
                        [
                            'page_name' => $params['page_name'],
                        ]
                    )*/
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
        $rootNode = self::findOne(['name' => $rootName]);

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

            foreach ($leaves as $node) {

                // prepare node identifiers
                $nodeOptions = [
                    'data-pageId' => $node->id,
                    'data-lvl'    => $node->lvl,
                ];

                $itemTemplate = [
                    'label'       => $node->name,
                    'url'         => $node->createUrl(),// TODO $node->createUrl(),
                    'active'      => $node->active,
                    'linkOptions' => $nodeOptions,
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