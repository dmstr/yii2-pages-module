<?php

namespace dmstr\modules\pages\views\treeview;

use insolita\wgadminlte\Box;
use insolita\wgadminlte\InfoBox;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\tree\TreeView;
use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @package yii2-tree-manager
 * @version 1.5.0
 *
 * @var $this  \yii\web\View
 * @var $form \kartik\form\ActiveForm
 * @var $node \dmstr\modules\pages\models\Tree
 * @var $params array
 * @var $isAdmin boolean
 * @var $keyAttribute string
 * @var $action string
 * @var $currUrl string
 * @var $modelClass string
 * @var $softDelete boolean
 * @var $iconsList string
 * @var $nameAttribute string
 * @var $iconAttribute string
 * @var $iconTypeAttribute string
 * @var $showFormButtons boolean
 */

$this->registerJs(
    "$(function () {
        $('[data-toggle=\'tooltip\']').tooltip({'html': false});
    });"
);

// Extract $_POST to @vars
extract($params);

// Set isAdmin @var
$isAdmin = ($isAdmin == true || $isAdmin === 'true');

if (empty($parentKey)) {
    $parent = $node->parents(1)->one();
    $parentKey = empty($parent) ? '' : Html::getAttributeValue($parent, $keyAttribute);
}

$inputOpts = [];
$flagOptions = ['class' => 'kv-parent-flag'];

if (!$node->isNewRecord) {
    if ($node->isReadonly()) {
        $inputOpts['readonly'] = true;
    }
    if ($node->isDisabled()) {
        $inputOpts['disabled'] = true;
    }
    $flagOptions['disabled'] = $node->isLeaf();
}

/*
 * Begin active form
 * @controller NodeController
 */
$form = ActiveForm::begin(['action' => $action]);

// Get tree manager module
$treeViewModule = TreeView::module();

// create node Url
$nodeUrl = $node->createUrl();

/** @var array $userAuthItems */
$userAuthItems = $node::getUsersAuthItems();

// In case you are extending this form, it is mandatory to set
// all these hidden inputs as defined below.
echo Html::hiddenInput(Html::getInputName($node, $keyAttribute), $node->id);
echo Html::hiddenInput('treeNodeModify', $node->isNewRecord);
echo Html::hiddenInput('parentKey', $parentKey);
echo Html::hiddenInput('currUrl', $currUrl);
echo Html::hiddenInput('modelClass', $modelClass);
echo Html::hiddenInput('softDelete', $softDelete);
?>


<?php $this->beginBlock('buttons') ?>
<?php if (empty($inputOpts['disabled']) || ($isAdmin && $showFormButtons)): ?>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::submitButton(
                FA::i(FA::_FLOPPY_O).' '.Yii::t('pages', 'Apply'),
                ['class' => 'btn btn-success']
            ) ?>
            <?= Html::resetButton(
                FA::i(FA::_REFRESH).' '.Yii::t('pages', 'Reset'),
                ['class' => 'btn btn-default pull-right']
            ) ?>
        </div>
    </div>
<?php endif; ?>
<?php $this->endBlock() ?>



<?php Box::begin(
    [
        #'title'    => Yii::t('pages', 'General'),
        'type'=> 'solid'
    ]
) ?>

<?= $this->blocks['buttons'] ?>

<div class="vertical-spacer"></div>

<h2>
    <?= FA::icon($node->icon?:'file') ?>
    <?= $node->name ?>
    <small>
        <span class="label label-default"><?= $node->getNameId() ?></span>
    </small>
</h2>

<p><?= Html::a($nodeUrl, $nodeUrl) ?></p>

<div class="clearfix"></div>

<?php if ($iconsList == 'text' || $iconsList == 'none') : ?>

        <?php Box::begin(
            [
                #'title'    => Yii::t('pages', 'General'),
                'type'=> Box::TYPE_PRIMARY
            ]
        ) ?>
        <div class="row">
            <div class="col-xs-12 col-lg-5">
                <?= $form->field($node, $node::ATTR_ROUTE)->widget(
                    Select2::classname(),
                    [

                        'data' => $node::optsRoute(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                );
                ?>
            </div>
            <div class="col-xs-12 col-lg-7">
                <?= $form->field($node, $node::ATTR_VIEW)->widget(
                    Select2::classname(),
                    [

                        'data' => $node::optsView(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                ); ?>
            </div>

            <div class="col-sm-5">
                <?php if (isset($treeViewModule->treeViewSettings['fontAwesome']) && $treeViewModule->treeViewSettings['fontAwesome'] == true): ?>
                    <?= $form->field($node, $iconAttribute)->widget(
                        Select2::classname(),
                        [

                            'data' => $node::optsIcon(true),
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => [
                                'escapeMarkup' => new \yii\web\JsExpression('function(m) { return m; }'),
                                'allowClear' => true,
                            ],
                        ]
                    ) ?>
                <?php else: ?>
                    <?= $form->field($node, $iconAttribute
                    )->textInput($inputOpts) ?>
                <?php endif; ?>
            </div>


            <div class="col-xs-12 col-sm-7">
                <?= $form->field($node, $node::ATTR_DOMAIN_ID
                )->textInput() ?>
            </div>

        </div>
        <?php Box::end() ?>


        <?php Box::begin(
            [
                #'title'           => Yii::t('pages', Yii::t('pages', 'Localization')),
            ]
        ) ?>
        <?php if ($node->getBehavior('translatable')->isFallbackTranslation): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="well well-sm alert-info"><!-- using well instead of alert to not conflict with kv treeview JS -->
                    <?= \Yii::t('pages', 'The currently displayed values are taken from the fallback language. If you change translated values a new translation will be stored for this page.') ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($node, $node::ATTR_NAME) ?>
            </div>

            <div class="col-xs-12">
                <?= $form->field($node, $node::ATTR_PAGE_TITLE)->textInput($inputOpts) ?>
            </div>
        </div>

    <div class="row">
            <div class="col-xs-12 col-lg-12">
                <?= $form->field($node, $node::ATTR_DEFAULT_META_KEYWORDS)->textInput() ?>
            </div>
            <div class="col-xs-12 col-lg-12">
                <?= $form->field($node, $node::ATTR_DEFAULT_META_DESCRIPTION)->textarea(['rows' => 5]) ?>
            </div>
        </div>


    <hr>
<h4><?= Yii::t('pages', 'Access') ?></h4>

    <div class="row">
        <div class="col-xs-12 col-sm-2">
            <?php
            // set default value if value is null (translation_meta entry missing)
            $node->visible = $node->isVisible() ? 1 : 0;
            echo $form->field($node, 'visible')->checkbox()
            ?>
        </div>
        <div class="col-xs-12 col-sm-2">
            <?php
            // set default value if value is null (translation_meta entry missing)
            $node->disabled = $node->isDisabled() ? 1 : 0;
            echo $form->field($node, 'disabled')->checkbox()
            ?>
        </div>
        <div class="col-xs-12 col-sm-2">
            <?= $form->field($node, 'collapsed')->checkbox($flagOptions) ?>
        </div>
    </div>


        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <?= $form->field($node, $node::ATTR_ACCESS_DOMAIN)->widget(
                    Select2::classname(),
                    [

                        'data' => $node::optsAccessDomain(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                ) ?>
            </div>
        </div>
            <div class="row">
            <div class="col-xs-12 col-sm-4">
                <?= $form->field($node, $node::ATTR_ACCESS_READ)->widget(
                    Select2::classname(),
                    [

                        'data' => $userAuthItems,
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                )

                ?>
            </div>
            <div class="col-xs-12 col-sm-4">
                <?php if ($node->hasPermission($node::ATTR_ACCESS_UPDATE) || $node->isNewRecord) : ?>
                    <?= $form->field($node, $node::ATTR_ACCESS_UPDATE)->widget(
                        Select2::classname(),
                        [

                            'data' => $userAuthItems,
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => ['allowClear' => true],
                        ]
                    )

                    ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-sm-4">
                <?php if ($node->hasPermission($node::ATTR_ACCESS_DELETE) || $node->isNewRecord) : ?>
                    <?= $form->field($node, $node::ATTR_ACCESS_DELETE)->widget(
                        Select2::classname(),
                        [

                            'data' => $userAuthItems,
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => ['allowClear' => true],
                        ]
                    )

                    ?>
                <?php endif; ?>
            </div>
        </div>

        <?php Box::end() ?>

        <?php Box::begin(
            [
                #'title'             => Yii::t('pages', Yii::t('pages', 'Advanced')),
                'type' => Box::TYPE_PRIMARY
            ]
        ) ?>
        <div class="row">


            <div class="col-xs-12">
                <?= $form->field($node, $node::ATTR_REQUEST_PARAMS
                )->widget(\devgroup\jsoneditor\Jsoneditor::className(), ['model' => $node, 'attribute' => $node::ATTR_REQUEST_PARAMS]) ?>
            </div>

            <div class="col-sm-6">
                <?= $form->field($node, $iconTypeAttribute)->widget(
                    Select2::classname(),
                    [

                        'data' => [
                            TreeView::ICON_CSS => 'CSS Suffix',
                            TreeView::ICON_RAW => 'Raw Markup',
                        ],
                        'options' => [
                                'id' => 'tree-'.$iconTypeAttribute,
                                'placeholder' => Yii::t('pages', 'Select'),
                                'multiple' => false,
                            ] + $inputOpts,
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                    ]
                );
                ?>
            </div>
        </div>
        <?php Box::end() ?>

<?php else : ?>
    <div class="row">
        <div class="col-sm-6">
            <?= Html::activeHiddenInput($node, $iconTypeAttribute) ?>
            <?= $form->field(
                $node,
                $nameAttribute
            )->textArea(['rows' => 2] + $inputOpts) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field(
                $node,
                $iconAttribute
            )->multiselect(
                $iconsList,
                [
                    'item' => function ($index, $label, $name, $checked, $value) use ($inputOpts) {
                        if ($index == 0 && $value == '') {
                            $checked = true;
                            $value = '';
                        }

                        return '<div class="radio">'.Html::radio(
                            $name,
                            $checked,
                            [
                                'value' => $value,
                                'label' => $label,
                                'disabled' => !empty($inputOpts['readonly']) || !empty($inputOpts['disabled']),
                            ]
                        ).'</div>';
                    },
                    'selector' => 'radio',
                ]
            ) ?>
        </div>
    </div>
<?php endif; ?>

<?= $this->blocks['buttons'] ?>


    <?php Box::end() ?>
<?php ActiveForm::end() ?>
