<?php
/**
 * Output TreeView widget
 *
 * @var $this yii\web\View
 */
use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use yii\helpers\Inflector;

$this->title = Inflector::titleize($this->context->module->id);

/**
 * Wrapper templates
 */
$headerTemplate = <<< HTML
<div class="row">
    <div class="col-sm-6" id="pages-detail-heading">
        {heading}
    </div>
    <div class="col-sm-6" id="pages-detail-search">
        {search}
    </div>
</div>
HTML;

$mainTemplate = <<< HTML
<div class="row">
    <div class="col-md-4" id="pages-detail-wrapper">
        <div class="box boy-body">
        {wrapper}
        </div>
    </div>
    <div class="col-md-8" id="pages-detail-panel">
        {detail}
    </div>
</div>
HTML;

/** @var Tree $queryTree */
$queryTree = Tree::find()
    ->where(
        [
            Tree::ATTR_ACCESS_DOMAIN => [
                \Yii::$app->language,
                (Yii::$app->user->can(Tree::GLOBAL_ACCESS_PERMISSION) ? Tree::GLOBAL_ACCESS_DOMAIN : '')
            ]
        ]
    )
    ->orderBy('root, lft');

/**
 * Render tree view
 */
echo TreeView::widget(
    [
        'query' => $queryTree,
        'isAdmin' => true,
        'softDelete' => false,
        'displayValue' => 1,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => 'Nodes'],
        'treeOptions' => ['style' => 'height:auto; min-height:400px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
    ]
);
