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

/**
 * @var yii\web\View $this
 * @var kartik\tree\models\Tree $node
 * @var kartik\form\ActiveForm $form
 */

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

<?php
/**
 * Begin output form
 */
if (empty($inputOpts['disabled']) || ($isAdmin && $showFormButtons)): ?>
    <div class="pull-right">
        <?= Html::resetButton(
            '<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('kvtree', 'Reset'),
            ['class' => 'btn btn-default']
        ) ?>
        <?= Html::submitButton(
            '<i class="glyphicon glyphicon-floppy-disk"></i> ' . Yii::t('kvtree', 'Save'),
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
<?php endif; ?>


    <h3><?= $name . " <small>#" . $node->id . "</small>" ?></h3>
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
        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
            <?php if (isset($module->treeViewSettings['fontAwesome']) && $module->treeViewSettings['fontAwesome'] == true): ?>
                <?php
                $escape = new \yii\web\JsExpression("function(m) { return m; }");
                $addon  = [
                    'prepend' => [
                        'content' => Inflector::titleize($iconAttribute)
                    ],
                ];
                echo $form->field($node, $iconAttribute)->widget(
                    \kartik\select2\Select2::classname(),
                    [
                        'name'          => $iconAttribute,
                        'model'         => $node,
                        'attribute'     => $iconAttribute,
                        'addon'         => $addon,
                        'data'          => FA::getConstants(true),
                        'options'       => [
                            'placeholder' => Yii::t('app', 'Type to autocomplete'),
                            'multiple'    => false,
                        ],
                        'pluginOptions' => [
                            'escapeMarkup' => $escape,
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
            <?= $form->field(
                $node,
                $iconTypeAttribute,
                [
                    'addon' => ['prepend' => ['content' => Inflector::titleize($iconTypeAttribute)]]
                ]
            )->dropdownList(
                [
                    TreeView::ICON_CSS => 'CSS Suffix',
                    TreeView::ICON_RAW => 'Raw Markup',
                ],
                $inputOpts
            )->label("") ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= "JSON Editor testing" .
            Jsoneditor::widget(
                [
                    'editorOptions' => [
                        'modes' => ['code', 'form', 'text', 'tree', 'view'], // available modes
                        'mode'  => 'tree', // current mode
                    ],
                    'name'          => 'XXX',
                    // input name. Either 'name', or 'model' and 'attribute' properties must be specified.
                    'options'       => [],
                    // html options
                ]
            );
            ?>
        </div>
    </div>
    <hr/><h4><?= Yii::t('kvtree', 'Title / Names') ?></h4>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___pageTitle___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___slug___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___name_id___
        </div>
    </div>
    <hr/><h4><?= Yii::t('kvtree', 'Route') ?></h4>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___controller___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___view___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___request_params___
        </div>
    </div>
    <hr/><h4><?= Yii::t('kvtree', 'SEO') ?></h4>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___default_meta_keywords___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___default_meta_description___
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
            ___default_meta_???___
        </div>
    </div>
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

<?= renderContent(Module::VIEW_PART_2); ?>

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