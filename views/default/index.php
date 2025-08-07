<?php
/**
 * Output TreeView widget
 *
 * @var yii\web\View $this
 * @var \yii\db\ActiveQuery $query
 * @var string $headerTemplate
 * @var string $mainTemplate
 * @var array $toolbar
 * @var int|string $pageId
 */
use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use yii\helpers\Inflector;

echo TreeView::widget(
    [
        'query' => $query,
        'isAdmin' => true,
        'softDelete' => false,
        'displayValue' => $pageId,
        'showTooltips' => false,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => Yii::t('pages', 'Nodes')],
        'treeOptions' => ['style' => 'height:auto; min-height:400px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
        'toolbar' => $toolbar
    ]
);
