<?php

namespace _;

/* @var $this yii\web\View */

use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Nav;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

?>

    <h1>Pages</h1>
    <h2>Test</h2>

<?php

echo Nav::widget(
    [
        'options' => ['class' => 'navbar-nav'],
        'encodeLabels' => false,
        'items' => \dmstr\modules\pages\models\Tree::getMenuItems('root_'.\Yii::$app->language),
    ]
);
?>

<hr>

<?php
VarDumper::dump($tree, 10, true);
?>