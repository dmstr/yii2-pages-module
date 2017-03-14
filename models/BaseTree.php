<?php
namespace dmstr\modules\pages\models;
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use dmstr\db\traits\ActiveRecordAccessTrait;
use dmstr\modules\pages\Module as PagesModule;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * Class BaseTree
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class BaseTree extends \kartik\tree\models\Tree
{
    use ActiveRecordAccessTrait;

    /**
     * Icon type css
     */
    const ICON_TYPE_CSS = 1;

    /**
     * Icon type raw
     */
    const ICON_TYPE_RAW = 2;

    /**
     * Node active
     */
    const ACTIVE = 1;

    /**
     * Node not active
     */
    const NOT_ACTIVE = 0;

    /**
     * Node selected
     */
    const SELECTED = 1;

    /**
     * Node not selected
     */
    const NOT_SELECTED = 0;

    /**
     * Node disabled
     */
    const DISABLED = 1;

    /**
     * Node not disabled
     */
    const NOT_DISABLED = 0;

    /**
     * Node read only
     */
    const READ_ONLY = 1;

    /**
     * Node not read only
     */
    const NOT_READ_ONLY = 0;

    /**
     * Node visible
     */
    const VISIBLE = 1;

    /**
     * Node not visible
     */
    const NOT_VISIBLE = 0;

    /**
     * Node collapsed
     */
    const COLLAPSED = 1;

    /**
     * Node not collapsed
     */
    const NOT_COLLAPSED = 0;

    /**
     * The root node domain_id prefix and level identifier.
     */
    const ROOT_NODE_PREFIX = 'root';

    /**
     * The root node level identifier.
     */
    const ROOT_NODE_LVL = 0;

    /**
     * The default page route
     */
    const DEFAULT_PAGE_ROUTE = '/pages/default/page';

    /**
     * Column attribute 'id'
     */
    const ATTR_ID = 'id';

    /**
     * Column attribute 'name'
     */
    const ATTR_NAME = 'name';

    /**
     * Column attribute 'domain_id'
     */
    const ATTR_DOMAIN_ID = 'domain_id';

    /**
     * Column attribute 'access_domain'
     */
    const ATTR_ACCESS_DOMAIN = 'access_domain';

    /**
     * Column attribute 'access_owner'
     */
    const ATTR_ACCESS_OWNER = 'access_owner';

    /**
     * Column attribute 'access_read'
     */
    const ATTR_ACCESS_READ = 'access_read';

    /**
     * Column attribute 'root'
     */
    const ATTR_ROOT = 'root';

    /**
     * Column attribute 'lvl'
     */
    const ATTR_LVL = 'lvl';

    /**
     * Column attribute 'route'
     */
    const ATTR_ROUTE = 'route';

    /**
     * Column attribute 'view'
     */
    const ATTR_VIEW = 'view';

    /**
     * Column attribute 'request_params'
     */
    const ATTR_REQUEST_PARAMS = 'request_params';

    /**
     * Column attribute 'icon'
     */
    const ATTR_ICON = 'icon';

    /**
     * Column attribute 'icon_type'
     */
    const ATTR_ICON_TYPE = 'icon_type';

    /**
     * Column attribute 'active'
     */
    const ATTR_ACTIVE = 'active';

    /**
     * Column attribute 'selected'
     */
    const ATTR_SELECTED = 'selected';

    /**
     * Column attribute 'disabled'
     */
    const ATTR_DISABLED = 'disabled';

    /**
     * Column attribute 'readonly'
     */
    const ATTR_READ_ONLY = 'readonly';

    /**
     * Column attribute 'visible'
     */
    const ATTR_VISIBLE = 'visible';

    /**
     * Column attribute 'collapsed'
     */
    const ATTR_COLLAPSED = 'collapsed';

    /**
     * Global identifier for a access_domain
     */
    const GLOBAL_ACCESS_DOMAIN = '*';

    /**
     * RBAC permission name to manage global access domain page nodes
     */
    const GLOBAL_ACCESS_PERMISSION = 'pages.globalAccess';

    /**
     * RBAC permission name to copy page root nodes
     */
    const COPY_ACCESS_PERMISSION = 'pages_copy';

    /**
     * Virtual attribute generated from "domain_id"_"access_domain".
     *
     * @var string
     */
    public $name_id;

    /**
     * The pages module instance
     *
     * @var PagesModule
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // set the pages module instance
        if (null === $this->module = \Yii::$app->getModule(PagesModule::NAME)) {
            throw new HttpException(404, 'Module "' . PagesModule::NAME . '" not found in ' . __METHOD__);
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmstr_page';
    }

    /**
     * Access checks of a page node
     *
     *  - 'access_domain'   enabled
     *  - 'access_owner'    enabled
     *  - 'access_read'     enabled
     *  - 'access_update'   disabled, not implemented yet!
     *  - 'access_delete'   disabled, not implemented yet!
     *
     * @return array
     */
    public static function accessColumnAttributes()
    {
        return [
            'domain' => self::ATTR_ACCESS_DOMAIN,
            'owner'  => self::ATTR_ACCESS_OWNER,
            'read'   => self::ATTR_ACCESS_READ,
            'update' => false,
            'delete' => false,
        ];
    }

    /**
     * @inheritdoc
     *
     * Use yii\behaviors\TimestampBehavior for created_at and updated_at attribute
     *
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'audit' => [
                    'class' => 'bedezign\yii2\audit\AuditTrailBehavior'
                ],
                'timestamp' =>[
                    'class'              => TimestampBehavior::className(),
                    'createdAtAttribute' => 'created_at',
                    'updatedAtAttribute' => 'updated_at',
                    'value'              => new Expression('NOW()'),
                ],
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
                    'domain_id',
                    'default',
                    'value' => function () {
                        return uniqid();
                    }
                ],
                [
                    ['domain_id', 'access_domain'],
                    'unique',
                    'targetAttribute' => ['domain_id', 'access_domain'],
                    'message' => \Yii::t('pages', 'Combination domain_id and access_domain must be unique!'),
                ],
                [
                    'domain_id',
                    'match',
                    'pattern' => '/^[a-z0-9_-]+$/',
                    'message' => \Yii::t('pages', '{0} should not contain any uppercase and special chars!',
                                         ['{attribute}'])
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
                    'max' => 255,
                ],
                [
                    'route',
                    'match',
                    'pattern' => '@^/[^/]@i',
                    'message' => \Yii::t('pages', '{0} should begin with one slash!', ['{attribute}'])
                ],
                [
                    'view',
                    'required',
                    'when' => function ($model) {
                        return $model->route === self::DEFAULT_PAGE_ROUTE;
                    },
                    'whenClient' => 'function (attribute, value) {
                        return $("#tree-route").find(":selected").val() == "' . self::DEFAULT_PAGE_ROUTE . '";
                    }',
                    'message' => 'Route ' . self::DEFAULT_PAGE_ROUTE . ' requires a view.'
                ],
                [
                    [
                        'default_meta_description',
                    ],
                    'string',
                    'max' => 160,
                ],
                [
                    [
                        'access_domain',
                    ],
                    'string',
                    'max' => 8,
                ],
                [
                    [
                        'access_domain',
                    ],
                    'default',
                    'value' => mb_strtolower(\Yii::$app->language),
                ],
                [
                    [
                        'root',
                        'access_owner',
                    ],
                    'integer',
                    'integerOnly' => true,
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
                    'safe',
                ],
            ]
        );
    }
}
