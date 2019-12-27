<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * Class TreeTranslationMeta
 * @package dmstr\modules\pages\models
 * @author Carsten Brandt <mail@cebe.cc>
 */
class TreeTranslationMeta extends ActiveRecord
{

    /**
     * @param $insert
     * @param $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(\Yii::$app->cache, 'pages');
    }

    public function afterDelete()
    {
        parent::afterDelete();
        TagDependency::invalidate(\Yii::$app->cache, 'pages');
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
        return '{{%dmstr_page_translation_meta}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_id'], 'integer'],
            [['language'], 'required'],
            [['disabled'], 'boolean'],
            [['visible'], 'boolean'],
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