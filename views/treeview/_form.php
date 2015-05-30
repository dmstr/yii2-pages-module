<?php
/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @package yii2-tree-manager
 * @version 1.5.0
 */

use kartik\form\ActiveForm;
use kartik\tree\Module;
use kartik\tree\TreeView;
use \yii\helpers\Inflector;
use \yii\helpers\Html;
use \rmrevin\yii\fontawesome\FA;
use devgroup\jsoneditor\Jsoneditor;
use \dmstr\modules\pages\models\Tree;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var kartik\tree\models\Tree $node
 * @var kartik\form\ActiveForm $form
 */

$this->registerCss(
    "
    i.fa {
        padding-right: 10px;
    }
    .hints {
        font-size: 12px;
        color: #888888;
    }
    .vertical-spacer {
        height: 25px;
    }
    "
);

$this->registerJs(
    "$(function () {
        $('[data-toggle=\'tooltip\']').tooltip({'html': false});
    });"
);

/**
 * Function to render custom contents defined in
 */
function renderContent($part)
{
    if (empty($nodeAddlViews[$part])) {
        return '';
    }
    $p         = $params;
    $p['form'] = $form;
    return $this->render($nodeAddlViews[$part], $p);
}

// Extract $_POST to @vars
extract($params);

// Set isAdmin @var
$isAdmin = ($isAdmin == true || $isAdmin === "true");

if (empty($parentKey)) {
    $parent    = $node->parents(1)->one();
    $parentKey = empty($parent) ? '' : Html::getAttributeValue($parent, $keyAttribute);
} elseif ($parentKey == 'root') {
    $parent = '';
} else {
    $parent = $modelClass::findOne($parentKey);
}

$parentName  = empty($parent) ? '' : $parent->$nameAttribute . ' &raquo; ';
$inputOpts   = [];
$flagOptions = ['class' => 'kv-parent-flag'];

if ($node->isNewRecord) {
    $name = Yii::t('kvtree', 'Untitled');
} else {
    $name = $node->$nameAttribute;
    if ($node->isReadonly()) {
        $inputOpts['readonly'] = true;
    }
    if ($node->isDisabled()) {
        $inputOpts['disabled'] = true;
    }
    $flagOptions['disabled'] = $node->isLeaf();
}

/**
 * Begin active form
 * @controller NodeController
 */
$form = ActiveForm::begin(['action' => $action]);

// Get tree manager module
$module = TreeView::module();

// In case you are extending this form, it is mandatory to set
// all these hidden inputs as defined below.
echo Html::hiddenInput("Tree[{$keyAttribute}]", $node->id);
echo Html::hiddenInput('treeNodeModify', $node->isNewRecord);
echo Html::hiddenInput('parentKey', $parentKey);
echo Html::hiddenInput('currUrl', $currUrl);
echo Html::hiddenInput('modelClass', $modelClass);
echo Html::hiddenInput('softDelete', $softDelete);
?>
    <div class="vertical-spacer"></div>
<?php if ($node->hasRoute()) {
    echo Html::a(
        '<i class="' . $node->icon . '"></i> ' . $node->name . ' <small>#' . $node->id . '</small>',
        Url::to(
            $node->createUrl(),
            [
                'target' => '_blank',
            ]
        ),
        [
            'class'       => 'btn btn-default',
            'data-toggle' => 'tooltip',
            'title'       => Yii::t('kvtree', 'Go to frontend')
        ]
    );

} else {
    echo "<label><h4><i class=\"{$node->icon}\"></i> {$node->name} <small>#{$node->id}</small></h4></label>";
}
/**
 * Begin output form
 */
if (empty($inputOpts['disabled']) || ($isAdmin && $showFormButtons)): ?>
    <div class="pull-right">
        <?= Html::submitButton(
            '<i class="glyphicon glyphicon-floppy-disk"></i> ' . Yii::t('kvtree', 'Save'),
            ['class' => 'btn btn-primary']
        ) ?>
        <?= Html::resetButton(
            '<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('kvtree', 'Reset'),
            ['class' => 'btn btn-default']
        ) ?>
    </div>
<?php endif; ?>
    <hr/>
    <div class="clearfix"></div>

<?= renderContent(Module::VIEW_PART_1); ?>

    <h4><?= Yii::t('kvtree', 'General') ?></h4>

<?php if ($iconsList == 'text' || $iconsList == 'none') : ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">

            <?= $form->field(
                $node,
                $nameAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($nameAttribute)]]
                ]
            )->textInput($inputOpts)->label("") ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            <?php if (isset($module->treeViewSettings['fontAwesome']) && $module->treeViewSettings['fontAwesome'] == true): ?>
                <?= $form->field($node, $iconAttribute)->widget(
                    \kartik\select2\Select2::classname(),
                    [
                        'name'          => 'Tree[' . $iconAttribute . ']',
                        'model'         => $node,
                        'attribute'     => $iconAttribute,
                        'addon'         => [
                            'prepend' => [
                                'content' => Inflector::titleize($iconAttribute)
                            ],
                        ],
                        'data'          => FA::getConstants(true),
                        'options'       => [
                            'id'          => 'tree-' . $iconAttribute,
                            'placeholder' => Yii::t('app', 'Type to autocomplete'),
                            'multiple'    => false,
                        ],
                        'pluginOptions' => [
                            'escapeMarkup' => new \yii\web\JsExpression("function(m) { return m; }"),
                            'allowClear'   => true
                        ]
                    ]
                )->label(""); ?>
            <?php else: ?>
                <?= $form->field(
                    $node,
                    $iconAttribute,
                    [
                        'addon' => ['prepend' => ['content' => Inflector::titleize($iconAttribute)]]
                    ]
                )->textInput($inputOpts)->label("") ?>
            <?php endif; ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            <?= $form->field($node, $iconTypeAttribute)->widget(
                \kartik\select2\Select2::classname(),
                [
                    'name'          => 'Tree[' . $iconTypeAttribute . ']',
                    'model'         => $node,
                    'attribute'     => $iconTypeAttribute,
                    'addon'         => [
                        'prepend' => [
                            'content' => Inflector::titleize($iconTypeAttribute)
                        ],
                    ],
                    'data'          => [
                        TreeView::ICON_CSS => 'CSS Suffix',
                        TreeView::ICON_RAW => 'Raw Markup',
                    ],
                    'options'       => [
                            'id'          => 'tree-' . $iconTypeAttribute,
                            'placeholder' => Yii::t('app', 'Select'),
                            'multiple'    => false,
                        ] + $inputOpts,
                    'pluginOptions' => [
                        'allowClear' => false
                    ]
                ]
            )->label("");
            ?>
        </div>
    </div>

    <hr/><h4><?= Yii::t('kvtree', 'Title / Names') ?></h4>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
            <?= $form->field(
                $node,
                'page_title',
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize('page_title')]]
                ]
            )->textInput($inputOpts)->label("") ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
            <?= $form->field(
                $node,
                'name_id',
                [
                    'addon' => ['prepend' => ['content' => 'Name ID']]
                ]
            )->textInput()->label("") ?>
        </div>
    </div>

    <hr/><h4><?= Yii::t('kvtree', 'Route') ?></h4>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-6">
            <?= $form->field($node, Tree::ATTR_ACCESS_DOMAIN)->widget(
                \kartik\select2\Select2::classname(),
                [
                    'name'          => Html::getInputName($node, Tree::ATTR_ACCESS_DOMAIN),
                    'model'         => $node,
                    'attribute'     => Tree::ATTR_ACCESS_DOMAIN,
                    'addon'         => [
                        'prepend' => [
                            'content' => 'Access Domain'
                        ],
                    ],
                    'data'          => Tree::optsAccessDomain(),
                    'options'       => [
                        'id'          => 'tree-access_domain',
                        'placeholder' => Yii::t('app', 'Select language'),
                        'multiple'    => false,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]
            )->label("");
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-7">
            <?= $form->field($node, Tree::ATTR_ROUTE)->widget(
                \kartik\select2\Select2::classname(),
                [
                    'name'          => Html::getInputName($node, Tree::ATTR_ROUTE),
                    'model'         => $node,
                    'attribute'     => Tree::ATTR_ROUTE,
                    'addon'         => [
                        'prepend' => [
                            'content' => 'Controller / View'
                        ],
                    ],
                    'data'          => Tree::optsRoute(),
                    'options'       => [
                        'placeholder' => Yii::t('app', 'Select route'),
                        'multiple'    => false,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]
            )->label("");
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-7">
            <?= $form->field($node, Tree::ATTR_VIEW)->widget(
                \kartik\select2\Select2::classname(),
                [
                    'name'          => Html::getInputName($node, Tree::ATTR_VIEW),
                    'model'         => $node,
                    'attribute'     => Tree::ATTR_VIEW,
                    'addon'         => [
                        'prepend' => [
                            'content' => 'Available Views'
                        ],
                    ],
                    'data'          => Tree::optsView(),
                    'options'       => [
                        'id'          => 'tree-views',
                        'placeholder' => Yii::t('app', 'Type to autocomplete'),
                        'multiple'    => false,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]
            )->label(""); ?>
        </div>
    </div>

<!--    // TODO implement additional request params option-->
    <!--<div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">-->
<!--            <h5>--><?//= \Yii::t('kvtree', 'Additional Request params') ?><!--</h5>-->
<!--            --><?//=
            /*Jsoneditor::widget(
                [
                    'editorOptions' => [
                        'modes' => ['code', 'form', 'text', 'tree', 'view'], // available modes
                        'mode'  => 'tree', // current mode
                    ],
                    'model'         => $node,
                    'attribute'     => Tree::ATTR_REQUEST_PARAMS,
                    'options'       => [
                        'id'    => 'tree-request_params',
                        'class' => 'form-control',
                    ],
                    // html options
                ]
            );*/
           /* $js = <<<JS
$(document).ready(function () {
    // Event listener on key events in JSONEditor
    // TreeRequestParamsEditor -> Jsoneditor:43
    $('#tree-request_params-jsoneditor').on('keypress keydown keyup',function (e) {
        $('#tree-request_params').val(TreeRequestParamsEditor.getText());
    });
});
JS;*/
            // TODO enable when additional request params option is implemented
            // $this->registerJs($js);
            // ?>
        <!--</div>
    </div>-->

    <hr/><h4><?= Yii::t('kvtree', 'SEO') ?></h4>
    <?php if ($node->createUrl() != null) : ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?= $form->field(
                    $node,
                    'slug',
                    [
                        'addon' => [
                            'prepend' => [
                                'content' => \Yii::t('crud', 'Page URL')
                            ]
                        ]
                    ]
                )->textInput(
                    [
                        'value'    => Tree::getSluggedUrl($node),
                        'disabled' => true
                    ]
                )->label("")->hint(
                    FA::icon('info-circle') . ' ' .
                    \Yii::t(
                        'crud',
                        'Automatically generated from page title.'
                    ) . ' ' .
                    \Yii::t(
                        'crud',
                        'To change URL change page title above.'
                    ),
                    ['class' => 'hints']
                ) ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <?= $form->field(
                $node,
                'default_meta_keywords',
                [
                    'addon' => ['prepend' => ['content' => 'Keywords']]
                ]
            )->textInput()->label("") ?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <?= $form->field(
                $node,
                'default_meta_description',
                [
                    'addon' => ['prepend' => ['content' => 'Description']]
                ]
            )->textarea(['rows' => 5])->label("") ?>
        </div>
    </div>

    <?= renderContent(Module::VIEW_PART_2); ?>


<?php else : ?>
    <div class="row">
        <div class="col-sm-6">
            <?= Html::activeHiddenInput($node, $iconTypeAttribute) ?>
            <?= $form->field(
                $node,
                $nameAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($iconTypeAttribute)]]
                ]
            )->textArea(['rows' => 2] + $inputOpts)->label("") ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field(
                $node,
                $iconAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($iconTypeAttribute)]]
                ]
            )->multiselect(
                $iconsList,
                [
                    'item'     => function ($index, $label, $name, $checked, $value) use ($inputOpts) {
                        if ($index == 0 && $value == '') {
                            $checked = true;
                            $value   = '';
                        }
                        return '<div class="radio">' . Html::radio(
                            $name,
                            $checked,
                            [
                                'value'    => $value,
                                'label'    => $label,
                                'disabled' => !empty($inputOpts['readonly']) || !empty($inputOpts['disabled'])
                            ]
                        ) . '</div>';
                    },
                    'selector' => 'radio',
                ]
            )->label("") ?>
        </div>
    </div>
<?php endif; ?>
<?php
/**
 * ADMIN Settings
 */
if ($isAdmin): ?>
    <hr/><h4><?= Yii::t('kvtree', 'Admin Settings') ?></h4>
    <?= renderContent(Module::VIEW_PART_3); ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($node, 'active')->checkbox() ?>
            <?= $form->field($node, 'selected')->checkbox() ?>
            <?= $form->field($node, 'collapsed')->checkbox($flagOptions) ?>
            <?= $form->field($node, 'visible')->checkbox() ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($node, 'readonly')->checkbox() ?>
            <?= $form->field($node, 'disabled')->checkbox() ?>
            <?= $form->field($node, 'removable')->checkbox() ?>
            <?= $form->field($node, 'removable_all')->checkbox($flagOptions) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($node, 'movable_u')->checkbox() ?>
            <?= $form->field($node, 'movable_d')->checkbox() ?>
            <?= $form->field($node, 'movable_l')->checkbox() ?>
            <?= $form->field($node, 'movable_r')->checkbox() ?>
        </div>
    </div>

    <?= renderContent(Module::VIEW_PART_4); ?>
<?php endif; ?>
<?php ActiveForm::end() ?>
<?= renderContent(Module::VIEW_PART_5); ?>