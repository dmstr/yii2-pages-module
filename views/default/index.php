<?php
/**
 * Output TreeView widget
 *
 * @var yii\web\View $this
 * @var \yii\db\ActiveQuery $query
 * @var string $headerTemplate
 * @var string $mainTemplate
 * @var array $toolbar
 */
use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use yii\helpers\Inflector;

echo TreeView::widget(
    [
        'query' => $query,
        'isAdmin' => true,
        'softDelete' => false,
        'displayValue' => 1,
        'showTooltips' => false,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => Yii::t('pages', 'Nodes')],
        'treeOptions' => ['style' => 'height:auto; min-height:400px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
        'toolbar' => $toolbar
    ]
);
