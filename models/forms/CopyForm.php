<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace dmstr\modules\pages\models\forms;

use dmstr\modules\pages\models\Tree;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class CopyForm
 * @package dmstr\modules\pages\models\forms
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class CopyForm extends Model
{
    /**
     * @var integer
     */
    public $sourceRootId;

    /**
     * @var string
     */
    public $destinationLanguage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sourceRootId'        => \Yii::t('pages', 'Source Root'),
            'destinationLanguage' => \Yii::t('pages', 'Destination Language'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sourceRootId', 'destinationLanguage'], 'required'],
            ['sourceRootId', 'integer'],
            ['sourceRootId', 'validateSourceRootExists'],
            ['destinationLanguage', 'string'],
            ['destinationLanguage', 'validateDestinationLanguage'],
        ];
    }

    /**
     * Validate if the root node with the given ID exists
     * @param $attribute
     */
    public function validateSourceRootExists($attribute)
    {
        // disable access trait to find root nodes in all languages
        Tree::$activeAccessTrait = false;

        $tree = Tree::findOne($this->$attribute);

        if ($tree === null) {
            $this->addError($attribute, \Yii::t('pages', 'Root node does not exist'));
        }
    }

    /**
     * Validate if the target language exists
     * @param $attribute
     */
    public function validateDestinationLanguage($attribute)
    {
        // build available languages
         $availableLanguages = ArrayHelper::merge([Tree::GLOBAL_ACCESS_DOMAIN], array_map('strtolower', \Yii::$app->urlManager->languages));

        if (!in_array($this->$attribute, $availableLanguages, true)) {
            $this->addError($attribute, \Yii::t('pages', 'Target Language "{language}" node does not exist', ['language' => $this->$attribute]));
        }
    }
}
