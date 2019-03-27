<?php

namespace _;

/* @var $tree \dmstr\modules\pages\models\Tree */

use yii\bootstrap\Nav;
use yii\helpers\VarDumper;

?>

<div class="container">
    <h1>Pages</h1>
    <h2>Test</h2>

    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar navbar-default'],
            'encodeLabels' => false,
            'items' => $tree,
        ]
    ) ?>

    <hr class="clearfix">

    <?php
    VarDumper::dump($tree, 10, true);
    ?>

</div>
