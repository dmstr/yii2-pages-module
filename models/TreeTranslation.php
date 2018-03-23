<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use yii\db\ActiveRecord;
use bedezign\yii2\audit\AuditTrailBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * Class TreeTranslation
 * @package dmstr\modules\pages\models
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
class TreeTranslation extends ActiveRecord
{
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
            'class' => TimestampBehavior::class,
            'value' => new Expression('NOW()'),
        ];

        return $behaviors;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'dmstr_page_translation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_id'], 'integer'],
            [['language'], 'required'],
            [['name'], 'string', 'max' => 60],
            [
                [
                    BaseTree::ATTR_PAGE_TITLE,
                    BaseTree::ATTR_DEFAULT_META_KEYWORDS,
                ],
                'string',
                'max' => 255,
            ],
            [[BaseTree::ATTR_DEFAULT_META_DESCRIPTION], 'safe'],
            [
                ['page_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Tree::class,
                'targetAttribute' => ['page_id' => 'id']
            ]
        ];
    }

}