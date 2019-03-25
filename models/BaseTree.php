<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use dmstr\db\traits\ActiveRecordAccessTrait;
use dmstr\modules\pages\Module as PagesModule;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use bedezign\yii2\audit\AuditTrailBehavior;
use dosamigos\translateable\TranslateableBehavior;

/**
 * Class BaseTree
 *
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 *
 * @property \yii\db\ActiveQuery $translations
 * @property \yii\db\ActiveQuery $translationsMeta
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
     * Column attribute 'request_params'
     */
    const ATTR_REQUEST_PARAMS = 'request_params';

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
     * Column attribute 'access_update'
     */
    const ATTR_ACCESS_UPDATE = 'access_update';

    /**
     * Column attribute 'access_delete'
     */
    const ATTR_ACCESS_DELETE = 'access_delete';

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
     * Column attribute 'created_at'
     */
    const ATTR_CREATED_AT = 'created_at';

    /**
     * Column attribute 'updated_at'
     */
    const ATTR_UPDATED_AT = 'updated_at';

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
    const PAGES_ACCESS_PERMISSION = 'pages';

    /**
     * @var bool whether to HTML encode the tree node names. Defaults to `false`.
     */
    public $encodeNodeNames = false;

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
        return '{{%dmstr_page}}';
    }

    /**
     * Access checks of a page node
     *
     *  - 'access_domain'   enabled
     *  - 'access_owner'    enabled
     *  - 'access_read'     enabled
     *  - 'access_update'   enabled
     *  - 'access_delete'   enabled
     *
     * @return array
     */
    public static function accessColumnAttributes()
    {
        return [
            'domain' => self::ATTR_ACCESS_DOMAIN,
            'owner'  => self::ATTR_ACCESS_OWNER,
            'read'   => self::ATTR_ACCESS_READ,
            'update' => self::ATTR_ACCESS_UPDATE,
            'delete' => self::ATTR_ACCESS_DELETE,
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

        $behaviors = parent::behaviors();

        $behaviors['audit'] = [
            'class' => AuditTrailBehavior::class
        ];

        $behaviors['timestamp'] = [
            'class'              => TimestampBehavior::class,
            'createdAtAttribute' => self::ATTR_CREATED_AT,
            'updatedAtAttribute' => self::ATTR_UPDATED_AT,
            'value'              => new Expression('NOW()'),
        ];

        $behaviors['translatable'] = [
            'class' => TranslateableBehavior::class,
            'languageField' => 'language',
            'skipSavingDuplicateTranslation' => true,
            'translationAttributes' => [
                self::ATTR_NAME
            ],
            'deleteEvent' => ActiveRecord::EVENT_BEFORE_DELETE,
            'restrictDeletion' => TranslateableBehavior::DELETE_LAST,
        ];

        $behaviors['translation_meta'] = [
            'class' => TranslateableBehavior::class,
            'relation' => 'translationsMeta',
            'languageField' => 'language',
            'fallbackLanguage' => false,
            'skipSavingDuplicateTranslation' => false,
            'translationAttributes' => [
                self::ATTR_DISABLED,
                self::ATTR_VISIBLE,
            ],
            'deleteEvent' => ActiveRecord::EVENT_BEFORE_DELETE,
        ];

        return $behaviors;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(TreeTranslation::class, ['page_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslationsMeta()
    {
        return $this->hasMany(TreeTranslationMeta::class, ['page_id' => 'id']);
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
                    self::ATTR_DOMAIN_ID,
                    'default',
                    'value' => function () {
                        return uniqid();
                    }
                ],
                [
                    [
                        self::ATTR_ACCESS_READ,
                    ],
                    'default',
                    'value' => self::$_all
                ],
                [
                    [
                        self::ATTR_ACCESS_UPDATE,
                        self::ATTR_ACCESS_DELETE
                    ],
                    'default',
                    'value' => static::getDefaultAccessUpdateDelete()
                ],
                [
                    [self::ATTR_DOMAIN_ID, self::ATTR_ACCESS_DOMAIN],
                    'unique',
                    'targetAttribute' => [self::ATTR_DOMAIN_ID, self::ATTR_ACCESS_DOMAIN],
                    'message' => \Yii::t('pages', 'Combination ' . self::ATTR_DOMAIN_ID . ' and ' . self::ATTR_ACCESS_DOMAIN . ' must be unique!'),
                ],
                [
                    self::ATTR_DOMAIN_ID,
                    'match',
                    'pattern' => '/^[a-z0-9_-]+$/',
                    'message' => \Yii::t(
                        'pages',
                        '{0} should not contain any uppercase and special chars!', ['{attribute}']
                    )
                ],
                [
                    [
                        self::ATTR_DOMAIN_ID,
                        self::ATTR_ROUTE,
                        self::ATTR_ICON,
                        self::ATTR_REQUEST_PARAMS,
                        self::ATTR_ACCESS_READ,
                        self::ATTR_ACCESS_UPDATE,
                        self::ATTR_ACCESS_DELETE,
                    ],
                    'string',
                    'max' => 255,
                ],
                [
                    self::ATTR_ROUTE,
                    'match',
                    'pattern' => '@^/[^/]@i',
                    'message' => \Yii::t('pages', '{0} should begin with one slash!', ['{attribute}'])
                ],
                [
                    [
                        self::ATTR_NAME,
                    ],
                    'string',
                    'max' => 60,
                ],
                [
                    [
                        self::ATTR_ACCESS_DOMAIN,
                    ],
                    'string',
                    'max' => 8,
                ],
                [
                    [
                        self::ATTR_ACCESS_DOMAIN,
                    ],
                    'default',
                    'value' => mb_strtolower(\Yii::$app->language),
                ],
                [
                    [
                        self::ATTR_ROOT,
                        self::ATTR_ACCESS_OWNER,
                    ],
                    'integer',
                    'integerOnly' => true,
                ],
                [
                    [
                        self::ATTR_ROOT,
                        self::ATTR_ACCESS_OWNER,
                        self::ATTR_COLLAPSED,
                        self::ATTR_ICON_TYPE
                    ],
                    'filter',
                    'filter' => 'intval'
                ],
                [
                    [
                        self::ATTR_DOMAIN_ID,
                        self::ATTR_NAME,
                        self::ATTR_ROUTE,
                        self::ATTR_REQUEST_PARAMS,
                        self::ATTR_ACCESS_DOMAIN,
                        self::ATTR_ACCESS_OWNER,
                        self::ATTR_ACCESS_UPDATE,
                        self::ATTR_ACCESS_DELETE,
                        self::ATTR_CREATED_AT,
                        self::ATTR_UPDATED_AT,
                    ],
                    'safe',
                ],
            ]
        );
    }
}
