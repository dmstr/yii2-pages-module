<?php

namespace dmstr\modules\pages\views\treeview;

use dmstr\jsoneditor\JsonEditorWidget;
use dmstr\widgets\AccessInput;
use insolita\wgadminlte\Box;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\tree\TreeView;
use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

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
 * @var $iconsList array
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
$isAdmin = ($isAdmin === true || $isAdmin === 'true');

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
echo Html::hiddenInput('currUrl', Url::to(['/pages', 'pageId' => $node->id]));
echo Html::hiddenInput('modelClass', $modelClass);
echo Html::hiddenInput('softDelete', $softDelete);
?>


<?php $this->beginBlock('buttons') ?>
<?php if (empty($inputOpts['disabled']) || ($isAdmin && $showFormButtons)): ?>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::submitButton(
                FA::i(FA::_FLOPPY_O) . ' ' . Yii::t('pages', 'Apply'),
                ['class' => 'btn btn-success']
            ) ?>
        </div>
    </div>
<?php endif; ?>
<?php $this->endBlock() ?>



<?php Box::begin(
    [
        'type' => 'solid'
    ]
) ?>

<?= $this->blocks['buttons'] ?>


<h2 class="pull-left">
    <?= $nodeUrl ? Html::a(FA::icon($node->icon ?: 'file') . ' ' . $node->name, $nodeUrl) : $node->name ?>
</h2>

<p class="text-right">
    <br/>
    <?= $nodeUrl ?>
    <br/>
    <span class="label label-default"><?= $node->getNameId() ?></span>
    <span class="label label-default"><?= $node->id ?></span>
</p>


<div class="clearfix"></div>


<?php if ($iconsList === 'text' || $iconsList === 'none') : ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="panel panel-<?= $node->isDisabled() ? 'warning' : 'success' ?>">
                <div class="panel-heading">
                    <?php
                    // set default value if value is null (translation_meta entry missing)
                    $node->disabled = $node->isDisabled() ? 1 : 0;
                    echo $form->field($node, 'disabled')->dropDownList([0 => 'Online', 1 => 'Offline'])->label('Status');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php Box::begin() ?>

    <?php if ($node->getBehavior('translatable')->isFallbackTranslation): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="well well-sm alert-info">
                    <!-- using well instead of alert to not conflict with kv treeview JS -->
                    <?= \Yii::t('pages',
                        'The currently displayed values are taken from the fallback language. If you change translated values a new translation will be stored for this page.') ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?= Html::a(
             FA::icon(FA::_REMOVE). ' ' . \Yii::t('pages', 'Delete Translation'),
            ['/pages/crud/tree-translation/delete', 'id' => $node->getTranslation()->id],
            [
                'class' => 'btn btn-default pull-right',
                'data-confirm' => '' . \Yii::t('pages', 'Are you sure to delete the current translation?') . '',
                'data-method' => 'post',
            ]
        ); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($node, $node::ATTR_NAME) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-2">
            <?php
            // set default value if value is null (translation_meta entry missing)
            $node->visible = $node->isVisible() ? 1 : 0;
            echo $form->field($node, 'visible')->checkbox()
            ?>
        </div>
        <div class="col-xs-12 col-sm-2">
            <?= $form->field($node, 'collapsed')->checkbox($flagOptions) ?>
        </div>
    </div>

    <?php Box::end() ?>


    <?php Box::begin(
        [
            'type' => Box::TYPE_PRIMARY
        ]
    ) ?>

    <div class="row">

        <div class="col-xs-12">
            <?= $form->field($node, $node::ATTR_DOMAIN_ID) ?>
        </div>

        <div class="col-xs-12">
            <?= $form->field($node, $node::ATTR_ROUTE)->widget(
                Select2::class,
                [

                    'data' => $node::optsRoute(),
                    'options' => [
                        'placeholder' => Yii::t('pages', 'Select ...'),
                        'data-request-url' => Url::to(['/pages/default/resolve-route-to-schema']),
                        'data-editor-id' => 'tree-request_params-container'
                    ],
                    'pluginOptions' => ['allowClear' => true],
                ]
            );
            ?>
        </div>

        <div class="col-xs-12">
            <?= $form->field($node, $node::ATTR_REQUEST_PARAMS
            )->widget(JsonEditorWidget::class,
                [
                    'schema' => Json::decode($node->requestParamsSchema),
                    'id' => 'requestParamEditor',
                    'clientOptions' => [
                        'theme' => 'bootstrap3',
                        'ajax' => true,
                        'disable_collapse' => true
                    ]
                ]) ?>
        </div>

    </div>

    <div class="row">

        <div class="col-sm-8">
            <?php if (isset($treeViewModule->treeViewSettings['fontAwesome']) && $treeViewModule->treeViewSettings['fontAwesome'] === true): ?>
                <?= $form->field($node, $iconAttribute)->widget(
                    Select2::class,
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
                <?= $form->field($node, $iconAttribute)->textInput($inputOpts) ?>
            <?php endif; ?>
        </div>


        <div class="col-sm-4">
            <?= $form->field($node, $iconTypeAttribute)->widget(
                Select2::class,
                [

                    'data' => [
                        TreeView::ICON_CSS => 'CSS Suffix',
                        TreeView::ICON_RAW => 'Raw Markup',
                    ],
                    'options' => [
                            'id' => 'tree-' . $iconTypeAttribute,
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

<?php Box::begin([
        'type' => Box::TYPE_WARNING
    ])?>

    <div class="row">


        <div class="col-xs-12">
            <div class="text-warning">
                <?=Yii::t('pages','{icon} Access permissions only effect displaying menu items, not accessing the route itself.',['icon' => FA::icon(FA::_WARNING)])?>
            </div>
        </div>
        <div class="col-xs-12">
            <?= AccessInput::widget(
                [
                    'form' => $form,
                    'model' => $node
                ]) ?>
        </div>


    </div>

<?php Box::end(); ?>

<?php else : ?>
    <div class="row">
        <div class="col-sm-6">
            <?= Html::activeHiddenInput($node, $iconTypeAttribute) ?>
            <?= $form->field($node, $nameAttribute)->textarea(['rows' => 2] + $inputOpts) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($node, $iconAttribute)->multiselect(
                $iconsList,
                [
                    'item' => function ($index, $label, $name, $checked, $value) use ($inputOpts) {
                        if ($index == 0 && $value == '') {
                            $checked = true;
                            $value = '';
                        }

                        return '<div class="radio">' . Html::radio(
                                $name,
                                $checked,
                                [
                                    'value' => $value,
                                    'label' => $label,
                                    'disabled' => !empty($inputOpts['readonly']) || !empty($inputOpts['disabled']),
                                ]
                            ) . '</div>';
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
