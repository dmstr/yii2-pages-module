<?php
/* @var $this yii\web\View */

use kartik\tree\TreeView;
use kartik\tree\TreeViewInput;
use dmstr\modules\pages\models\Tree;
use\yii\helpers\Inflector;

$title = Inflector::titleize($this->context->module->id);
\dmstr\modules\pages\assets\PagesAsset::register($this);

?>

<?php
/**
 * Output TreeView widget
 */

// Wrapper templates
$headerTemplate = <<< HTML
<div class="row">
    <div class="col-sm-6">
        {heading}
    </div>
    <div class="col-sm-6">
        {search}
    </div>
</div>
HTML;

$mainTemplate = <<< HTML
<div class="row">
    <div class="col-md-4">
        {wrapper}
    </div>
    <div class="col-md-8">
        {detail}
    </div>
</div>
HTML;

echo TreeView::widget(
    [
        'query'           => Tree::find()->addOrderBy('root, lft')->andWhere([Tree::ATTR_ACCESS_DOMAIN => \Yii::$app->language]),
        'isAdmin'         => true,
        'softDelete'      => false,
        'displayValue'    => 1,
        'wrapperTemplate' => "{header}{footer}{tree}",
        'headingOptions'  => ['label' => $title . '-Module'],
        'treeOptions'     => ['style' => 'height:600px'],
        'headerTemplate'  => $headerTemplate,
        'mainTemplate'    => $mainTemplate,
    ]
);
