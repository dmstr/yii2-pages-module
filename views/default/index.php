<?php
/* @var $this yii\web\View */

use kartik\tree\TreeView;
use dmstr\modules\pages\models\Tree;

echo "<h1>Pages</h1>";

echo TreeView::widget(
    [
        // single query fetch to render the tree
        'query'          => Tree::find()->addOrderBy('root, lft'),
        'headingOptions' => ['label' => 'Categories'],
        'isAdmin'        => true,         // optional (toggle to enable admin mode)
        'displayValue'   => 1,        // initial display value
        'softDelete'     => false,    // normally not needed to change
        //'cacheSettings' => ['enableCache' => true] // normally not needed to change
    ]
);