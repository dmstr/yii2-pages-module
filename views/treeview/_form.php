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
<div class="vertical-spacer"></div>

<?php if ($nodeUrl !== null) : ?>
    <?php $infoBoxHtml = InfoBox::widget(
        [
            'text' => '<div class="text-center">
                            <h3 style="white-space: normal;">'.$node->name.'</h3>
                            <div style="text-transform: lowercase">' . $nodeUrl . '</div>
                        </div>',
            'boxBg' => InfoBox::TYPE_AQUA,
            'icon' => (empty($node->icon)
                ? FA::$cssPrefix.' '.FA::$cssPrefix.'-file'
                : FA::$cssPrefix.' '.FA::$cssPrefix.'-'.$node->icon),
        ]
    );
    echo Html::a($infoBoxHtml, $nodeUrl);
    ?>
<?php endif; ?>
<div class="clearfix"></div>

<?php if ($iconsList == 'text' || $iconsList == 'none') : ?>
        <?php Box::begin(
            [
                'title'    => Yii::t('pages', 'General'),
            ]
        ) ?>
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($node, $node::ATTR_NAME,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($node::ATTR_NAME)]],
                    ]
                )->label(false) ?>
            </div>

            <div class="col-sm-6">
                <?php if (isset($treeViewModule->treeViewSettings['fontAwesome']) && $treeViewModule->treeViewSettings['fontAwesome'] == true): ?>
                    <?= $form->field($node, $iconAttribute)->widget(
                        Select2::classname(),
                        [
                            'addon' => [
                                'prepend' => ['content' => Inflector::titleize($iconAttribute)],
                            ],
                            'data' => $node::optsIcon(true),
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => [
                                'escapeMarkup' => new \yii\web\JsExpression('function(m) { return m; }'),
                                'allowClear' => true,
                            ],
                        ]
                    )->label(false); ?>
                <?php else: ?>
                    <?= $form->field($node, $iconAttribute,
                        [
                            'addon' => ['prepend' => ['content' => Inflector::titleize($iconAttribute)]],
                        ]
                    )->textInput($inputOpts)->label(false) ?>
                <?php endif; ?>
            </div>

        </div>
        <?php Box::end() ?>
        <?php Box::begin(
            [
                'title'    => Yii::t('pages', 'Route'),
            ]
        ) ?>
        <div class="row">
            <div class="col-xs-12 col-sm-5">
                <?= $form->field($node, $node::ATTR_ROUTE)->widget(
                    Select2::classname(),
                    [
                        'addon' => [
                            'prepend' => ['content' => Inflector::titleize($node::ATTR_ROUTE)],
                        ],
                        'data' => $node::optsRoute(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                )->label(false);
                ?>
            </div>
            <div class="col-xs-12 col-sm-7">
                <?= $form->field($node, $node::ATTR_VIEW)->widget(
                    Select2::classname(),
                    [
                        'addon' => [
                            'prepend' => ['content' => Inflector::titleize($node::ATTR_VIEW)],
                        ],
                        'data' => $node::optsView(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                )->label(false); ?>
            </div>

        </div>
        <?php Box::end() ?>
        <?php Box::begin(
            [
                'title'           => Yii::t('pages', Yii::t('pages', 'SEO')),
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
            <div class="col-xs-12">
                <?= $form->field($node, $node::ATTR_PAGE_TITLE,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($node::ATTR_PAGE_TITLE)]],
                    ]
                )->textInput($inputOpts)->label(false) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <?= $form->field($node, $node::ATTR_DEFAULT_META_KEYWORDS,
                    [
                        'addon' => ['prepend' => ['content' => \Yii::t('pages', 'Keywords')]],
                    ]
                )->textInput()->label(false) ?>
            </div>
            <div class="col-xs-12 col-lg-12">
                <?= $form->field($node, $node::ATTR_DEFAULT_META_DESCRIPTION,
                    [
                        'addon' => ['prepend' => ['content' => \Yii::t('pages', 'Description')]],
                    ]
                )->textarea(['rows' => 5])->label(false) ?>
            </div>
        </div>
        <?php Box::end() ?>
        <?php Box::begin(
            [
                'title' => Yii::t('pages', Yii::t('pages', 'Access')),
            ]
        ) ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <?= $form->field($node, $node::ATTR_ACCESS_DOMAIN)->widget(
                    Select2::classname(),
                    [
                        'addon' => [
                            'prepend' => [
                                'content' => Inflector::titleize($node::ATTR_ACCESS_DOMAIN),
                            ],
                        ],
                        'data' => $node::optsAccessDomain(),
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                )->label(false) ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <?= $form->field($node, $node::ATTR_ACCESS_READ)->widget(
                    Select2::classname(),
                    [
                        'addon' => [
                            'prepend' => [
                                'content' => Inflector::titleize($node::ATTR_ACCESS_READ),
                            ],
                        ],
                        'data' => $userAuthItems,
                        'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                        'pluginOptions' => ['allowClear' => true],
                    ]
                )
                    ->label(false)
                ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <?php if ($node->hasPermission($node::ATTR_ACCESS_UPDATE) || $node->isNewRecord) : ?>
                    <?= $form->field($node, $node::ATTR_ACCESS_UPDATE)->widget(
                        Select2::classname(),
                        [
                            'addon' => [
                                'prepend' => [
                                    'content' => Inflector::titleize($node::ATTR_ACCESS_UPDATE),
                                ],
                            ],
                            'data' => $userAuthItems,
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => ['allowClear' => true],
                        ]
                    )
                        ->label(false)
                    ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <?php if ($node->hasPermission($node::ATTR_ACCESS_DELETE) || $node->isNewRecord) : ?>
                    <?= $form->field($node, $node::ATTR_ACCESS_DELETE)->widget(
                        Select2::classname(),
                        [
                            'addon' => [
                                'prepend' => [
                                    'content' => Inflector::titleize($node::ATTR_ACCESS_DELETE),
                                ],
                            ],
                            'data' => $userAuthItems,
                            'options' => ['placeholder' => Yii::t('pages', 'Select ...')],
                            'pluginOptions' => ['allowClear' => true],
                        ]
                    )
                        ->label(false)
                    ?>
                <?php endif; ?>
            </div>
        </div>
        <?php Box::end() ?>
        <?php Box::begin(
            [
                'title'           => Yii::t('pages', 'Options'),
            ]
        ) ?>
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
        <?php Box::end() ?>
        <?php Box::begin(
            [
                'title'             => Yii::t('pages', Yii::t('pages', 'Advanced')),
            ]
        ) ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <?= $form->field($node, $node::ATTR_DOMAIN_ID,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($node::ATTR_DOMAIN_ID)]],
                    ]
                )->textInput()->label(false) ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($node, 'name_id',
                    [
                        'addon' => ['prepend' => ['content' => 'Name ID']],
                    ]
                )->textInput(['value' => $node->getNameId(), 'disabled' => 'disabled'])->label(false) ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($node, $iconTypeAttribute)->widget(
                    Select2::classname(),
                    [
                        'addon' => [
                            'prepend' => [
                                'content' => Inflector::titleize($iconTypeAttribute),
                            ],
                        ],
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
                )->label(false);
                ?>
            </div>
            <div class="col-xs-12">
                <?= $form->field($node, $node::ATTR_REQUEST_PARAMS,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($node::ATTR_REQUEST_PARAMS)]],
                    ]
                )->widget(\devgroup\jsoneditor\Jsoneditor::className(), ['model' => $node, 'attribute' => $node::ATTR_REQUEST_PARAMS])->label(false) ?>
            </div>
        </div>
        <?php Box::end() ?>

<?php else : ?>
    <div class="row">
        <div class="col-sm-6">
            <?= Html::activeHiddenInput($node, $iconTypeAttribute) ?>
            <?= $form->field(
                $node,
                $nameAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($iconTypeAttribute)]],
                ]
            )->textArea(['rows' => 2] + $inputOpts)->label(false) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field(
                $node,
                $iconAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($iconTypeAttribute)]],
                ]
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
            )->label(false) ?>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($inputOpts['disabled']) || ($isAdmin && $showFormButtons)): ?>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::submitButton(
                FA::i(FA::_FLOPPY_O).' '.Yii::t('pages', 'Save'),
                ['class' => 'btn btn-lg btn-primary']
            ) ?>
            <?= Html::resetButton(
                FA::i(FA::_REFRESH).' '.Yii::t('pages', 'Reset'),
                ['class' => 'btn btn-lg btn-default']
            ) ?>
        </div>
    </div>
<?php endif; ?>

<?php ActiveForm::end() ?>
