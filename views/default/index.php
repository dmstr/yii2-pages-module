<?php
/* @var $this yii\web\View */

use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use yii\helpers\Inflector;

$this->title = Inflector::titleize($this->context->module->id);
\dmstr\modules\pages\assets\PagesAsset::register($this);

?>

<?php
/**
 * Output TreeView widget.
 */

// Wrapper templates
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


echo TreeView::widget(
    [
        'query' => Tree::find()->addOrderBy('root, lft')->andWhere([
            Tree::ATTR_ACCESS_DOMAIN => [
                \Yii::$app->language,
                (Yii::$app->user->can(Tree::GLOBAL_ACCESS_PERMISSION)?Tree::GLOBAL_ACCESS_DOMAIN:'')
            ]
        ]),
        'isAdmin' => true,
        'softDelete' => false,
        'displayValue' => 1,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => 'Nodes'],
        'treeOptions' => ['style' => 'height:600px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
    ]
);
