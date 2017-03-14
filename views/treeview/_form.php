<?php

namespace dmstr\modules\pages\views\treeview;

/*
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @package yii2-tree-manager
 * @version 1.5.0
 */

use insolita\wgadminlte\Box;
use insolita\wgadminlte\SmallBox;
use kartik\form\ActiveForm;
use kartik\tree\TreeView;
use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @var $this  \yii\web\View
 * @var $form \kartik\form\ActiveForm
 * @var $node \dmstr\modules\pages\models\Tree
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
    <?= SmallBox::widget(
        [
            'head'        => $node->name,
            'type'        => SmallBox::TYPE_GRAY,
            'text'        => $nodeUrl,
            'icon'        => FA::$cssPrefix.' '.FA::$cssPrefix.'-'.$node->icon,
            'footer'      => 'Open',
            'footer_link' => $nodeUrl
        ]
    ) ?>
<?php endif; ?>
<div class="clearfix"></div>

<?php if ($iconsList == 'text' || $iconsList == 'none') : ?>
        <?php Box::begin(
            [
                'title'    => Yii::t('pages', 'General'),
                'collapse' => true
            ]
        ) ?>
        <div class="row">
            <div class="col-sm-6">

                <?= $form->field($node, $nameAttribute,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($nameAttribute)]],
                    ]
                )->textInput($inputOpts)->label(false) ?>
            </div>

            <div class="col-sm-6">
                <?php if (isset($treeViewModule->treeViewSettings['fontAwesome']) && $treeViewModule->treeViewSettings['fontAwesome'] == true): ?>
                    <?= $form->field($node, $iconAttribute)->widget(
                        \kartik\select2\Select2::classname(),
                        [
                            'model' => $node,
                            'attribute' => $iconAttribute,
                            'addon' => [
                                'prepend' => ['content' => Inflector::titleize($iconAttribute)],
                            ],
                            'data' => $node::optsIcon(true),
                            'options' => [
                                'id' => 'tree-'.$iconAttribute,
                                'placeholder' => Yii::t('pages', 'Type to autocomplete'),
                                'multiple' => false,
                            ],
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
                'title'           => Yii::t('pages', 'Options'),
                'collapse'          => true,
                'collapse_remember' => false,
                'collapseDefault'   => true
            ]
        ) ?>
        <div class="row">
            <div class="col-xs-12 col-sm-2">
                <?= $form->field($node, 'visible')->checkbox() ?>
            </div>
            <div class="col-xs-12 col-sm-2">
                <?= $form->field($node, 'disabled')->checkbox() ?>
            </div>
            <div class="col-xs-12 col-sm-2">
                <?= $form->field($node, 'collapsed')->checkbox($flagOptions) ?>
            </div>
        </div>
        <?php Box::end() ?>
        <?php if (true) : ?>
            <?php Box::begin(
                [
                    'title'    => Yii::t('pages', Yii::t('pages', 'Route')),
                    'collapse'          => true,
                    'collapse_remember' => false,
                    'collapseDefault'   => !$node->isPage()
                ]
            ) ?>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <?= $form->field($node, $node::ATTR_ACCESS_DOMAIN)->widget(
                        \kartik\select2\Select2::classname(),
                        [
                            'model' => $node,
                            'attribute' => $node::ATTR_ACCESS_DOMAIN,
                            'addon' => [
                                'prepend' => [
                                    'content' => Inflector::titleize($node::ATTR_ACCESS_DOMAIN),
                                ],
                            ],
                            'data' => $node::optsAccessDomain(),
                            'options' => [
                                'id' => 'tree-access-domain',
                                'placeholder' => Yii::t('pages', 'Type to autocomplete'),
                                'multiple' => false,
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ]
                    )->label(false) ?>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <?= $form->field($node, $node::ATTR_ROUTE)->widget(
                        \kartik\select2\Select2::classname(),
                        [
                            'model' => $node,
                            'attribute' => $node::ATTR_ROUTE,
                            'addon' => [
                                'prepend' => ['content' => Inflector::titleize($node::ATTR_ROUTE)],
                            ],
                            'data' => $node::optsRoute(),
                            'options' => [
                                'placeholder' => Yii::t('pages', 'Select route'),
                                'multiple' => false,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]
                    )->label(false);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <?= $form->field($node, $node::ATTR_VIEW)->widget(
                        \kartik\select2\Select2::classname(),
                        [
                            'model' => $node,
                            'attribute' => $node::ATTR_VIEW,
                            'addon' => [
                                'prepend' => ['content' => Inflector::titleize($node::ATTR_VIEW)],
                            ],
                            'data' => $node::optsView(),
                            'options' => [
                                'id' => 'tree-views',
                                'placeholder' => Yii::t('pages', 'Type to autocomplete'),
                                'multiple' => false,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]
                    )->label(false); ?>
                </div>

            </div>
            <?php Box::end() ?>

            <?php Box::begin(
                [
                    'title'           => Yii::t('pages', Yii::t('pages', 'SEO')),
                    'collapse'          => true,
                    'collapse_remember' => false,
                    'collapseDefault'   => !$node->isPage()
                ]
            ) ?>
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
            <?php if ($node->route && $nodeUrl !== null) : ?>
                <div class="row">
                    <div class="col-xs-12 col-lg-12">
                        <?= $form->field($node, $node::ATTR_SLUG,
                            [
                                'addon' => ['prepend' => ['content' => \Yii::t('pages', 'Page URL')]],
                            ]
                        )->textInput(
                            [
                                'value' => $nodeUrl,
                                'disabled' => true,
                            ]
                        )->label(false)->hint(
                            FA::icon('info-circle').' '.
                            \Yii::t('pages','Automatically generated from page title.')
                            .' '.
                            \Yii::t('pages','To change URL change page title above.'),
                            ['class' => 'hints']
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php Box::end() ?>

            <?php Box::begin(
                [
                    'title'             => Yii::t('pages', Yii::t('pages', 'Advanced')),
                    'collapse'          => true,
                    'collapse_remember' => false,
                    'collapseDefault'   => true
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
                        \kartik\select2\Select2::classname(),
                        [
                            'model' => $node,
                            'attribute' => $iconTypeAttribute,
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
        <?php endif; ?>


    <?php Box::begin(
        [
            'title' => Yii::t('pages', Yii::t('pages', 'Access')),
            'collapse' => true,
            'collapse_remember' => false,
            'collapseDefault' => true,
        ]
    ) ?>
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <?=
            $form
                ->field($node, $node::ATTR_ACCESS_READ)->widget(
                    \kartik\select2\Select2::classname(),
                    [
                        'model' => $node,
                        'attribute' => $node::ATTR_ACCESS_READ,
                        'addon' => [
                            'prepend' => [
                                'content' => Inflector::titleize($node::ATTR_ACCESS_READ),
                            ],
                        ],
                        'data' => $node::getUsersAuthItems(),
                        'options' => [
                            'placeholder' => Yii::t('pages', 'Select ...'),
                            'multiple' => false,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]
                )
                ->label(false)
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
                '<i class="glyphicon glyphicon-floppy-disk"></i> '.Yii::t('pages', 'Save'),
                ['class' => 'btn btn-lg btn-primary']
            ) ?>
            <?= Html::resetButton(
                '<i class="glyphicon glyphicon-repeat"></i> '.Yii::t('pages', 'Reset'),
                ['class' => 'btn btn-lg btn-default']
            ) ?>
        </div>
    </div>
<?php endif; ?>

<?php ActiveForm::end() ?>
