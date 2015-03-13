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
        'treeOptions'     => ['style' => 'height:500px'],
        'headerTemplate'  => $headerTemplate,
        'mainTemplate'    => $mainTemplate,
    ]
);
?>
<hr/>
<h3>InputWidget</h3>
<?php
echo TreeViewInput::widget(
    [
        // single query fetch to render the tree
        'query'          => Tree::find()->addOrderBy('root, lft'),
        'headingOptions' => ['label' => 'Categories'],
        'name'           => 'kv-product',    // input name
        'value'          => '1,2,3',         // values selected (comma separated for multiple select)
        'asDropdown'     => true,            // will render the tree input widget as a dropdown.
        'multiple'       => false,            // set to false if you do not need multiple selection
        'fontAwesome'    => true,            // render font awesome icons
        'rootOptions'    => [
            'label' => '<i class="fa fa-tree"></i>',
            'class' => 'text-success'
        ]
    ]
);
// custom root label
//'options'         => ['disabled' => true],
?>
<hr/>
<h3>TODOs</h3>

<ul>
    <li>
        <b>Datenmodell</b> <br/>
        <ul>
            <li>
                pageTitle NULL
            </li>
            <li>
                slug NULL
            </li>
            <li>
                name_id NOT NULL
            </li>
            <li>
                controller NULL (via config)
            </li>
            <li>
                view NULL (via config)
            </li>
            <li>
                default_meta_keywords NULL
            </li>
            <li>
                default_meta_description NULL
            </li>
            <li>
                request_params TEXT NULL
            </li>
            <li>
                ?? other seo fields
            </li>
        </ul>
    </li>
    <li>
        <b>Models / Views</b> <br/>
        <ul>
            <li>
                extend model attributes and validations <code>dmstr\modules\pages\models\Tree</code>
            </li>
            <li>
                update <code>_form</code> view
            </li>
            <li>
                ...
            </li>
        </ul>
    </li>
</ul>