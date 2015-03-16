<?php
/* @var $this yii\web\View */

use kartik\tree\TreeView;
use kartik\tree\TreeViewInput;
use dmstr\modules\pages\models\Tree;
use\yii\helpers\Inflector;

$title = Inflector::titleize($this->context->module->id);

/**
 * Output TreeView widget
 */

// Wrapper templates
$headerTemplate = <<< HTML
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        {heading}
    </div>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        {search}
    </div>
</div>
HTML;

$mainTemplate = <<< HTML
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
        {wrapper}
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-8">
        {detail}
    </div>
</div>
HTML;

echo TreeView::widget(
    [
        'query'           => Tree::find()->addOrderBy('root, lft'),
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
?>
<hr/>
<h3>TODOs</h3>

<ul>
    <li>
        <b>Add copy pages / </b> <br/>
        <ul>
            <li>
                with widgets
            </li>
            <li>
                without widgets
            </li>
            <li>
                recursive and single
            </li>
        </ul>
    </li>
</ul>