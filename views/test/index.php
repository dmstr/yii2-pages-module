<?php

namespace _;

/* @var $tree \dmstr\modules\pages\models\Tree */

use dmstr\modules\pages\models\Tree;
use yii\bootstrap\Nav;
use yii\helpers\VarDumper;

?>

    <h1>Pages</h1>
    <h2>Test</h2>

    <?php

echo Nav::widget(
    [
        'options' => ['class' => 'navbar-nav'],
        'encodeLabels' => false,
        'items' => Tree::getMenuItems(Tree::ROOT_NODE_PREFIX),
    ]
);
?>

    <hr>

    <?php
VarDumper::dump($tree, 10, true);
?>
