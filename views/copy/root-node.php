<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2017 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @var $copyForm CopyForm
 */

use dmstr\modules\pages\models\forms\CopyForm;
use dmstr\modules\pages\models\Tree;
use insolita\wgadminlte\Box;
use kartik\select2\Select2;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = \Yii::t('pages', 'Copy Root Node');

/**
 * Attribute names
 */
$attributeSourceRootId        = 'sourceRootId';
$attributeDestinationLanguage = 'destinationLanguage';
?>
<?php
Box::begin(
    [
        'title'    => Yii::t('pages', 'General'),
        'collapse' => false
    ]
);
$form = ActiveForm::begin();
?>
    <div class="row">
        <div class="col-xs-12 col-sm-4 col-lg-3">
            <?= $form->field($copyForm, $attributeSourceRootId)->widget(
                Select2::classname(),
                [
                    'name'          => Html::getInputName($copyForm, $attributeSourceRootId),
                    'model'         => $copyForm,
                    'attribute'     => $attributeSourceRootId,
                    'addon'         => [
                        'prepend' => [
                            'content' => FA::i('tree'),
                        ],
                    ],
                    'data'          => Tree::optsSourceRootId(),
                    'options'       => [
                        'placeholder' => Yii::t('pages', 'Select root node'),
                        'multiple'    => false,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]
            )->label(false); ?>
        </div>
        <div class="col-xs-12 col-sm-4 col-lg-3">
            <?= $form->field($copyForm, $attributeDestinationLanguage)->widget(
                Select2::classname(),
                [
                    'name'          => Html::getInputName($copyForm, $attributeDestinationLanguage),
                    'model'         => $copyForm,
                    'attribute'     => $attributeDestinationLanguage,
                    'addon'         => [
                        'prepend' => [
                            'content' => FA::i('flag'),
                        ],
                    ],
                    'data'          => Tree::optsAccessDomain(),
                    'options'       => [
                        'placeholder' => Yii::t('pages', 'Select target language'),
                        'multiple'    => false,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]
            )->label(false); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4 col-lg-3">
            <?= Html::submitButton(FA::i('copy').' '.\Yii::t('pages', 'Start copy'), ['class' => 'btn btn-info'])?>
            <?= Html::a('Tree Manager',['/pages'],['class' => 'btn btn-default'])?>
        </div>
    </div>
<?php ActiveForm::end();
Box::end();
