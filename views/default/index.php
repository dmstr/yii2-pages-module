<?php
/**
 * Output TreeView widget
 *
 * @var $this yii\web\View
 */
use dmstr\modules\pages\models\Tree;
use kartik\tree\TreeView;
use yii\helpers\Inflector;

$this->title = Inflector::titleize($this->context->module->id);

/**
 * Wrapper templates
 */
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

/**
 * Links to settings and copy area for toolbar
 */
$toolbar = [
    TreeView::BTN_SEPARATOR,
    TreeView::BTN_SEPARATOR,
    TreeView::BTN_SEPARATOR,
    'copy' => [
        'icon' => 'copy',
        'url' => (\Yii::$app->user->can(Tree::COPY_ACCESS_PERMISSION)) ? ['/pages/copy'] : null,
        'options' => [
            'title' => Yii::t('pages', 'Copy root nodes'),
            'disabled' => (\Yii::$app->user->can(Tree::COPY_ACCESS_PERMISSION) ? false : true),
            'class' => 'btn btn-success'
        ],
    ],
    TreeView::BTN_SEPARATOR,
    'settings' => [
        'icon' => 'cogs',
        'url' => (\Yii::$app->hasModule('settings')) ? ['/settings', 'SettingSearch' => ['section' => 'pages']] : null,
        'options' => [
            'title' => Yii::t('pages', 'Settings'),
            'disabled' => !\Yii::$app->hasModule('settings'),
            'class' => 'btn btn-info'
        ]
    ]
];

$mainTemplate = <<< HTML
<div class="row">
    <div class="col-md-4" id="pages-detail-wrapper">
        <div class="box">
        {wrapper}
        </div>
    </div>
    <div class="col-md-8" id="pages-detail-panel">
        {detail}
    </div>
</div>
HTML;

/** @var Tree $queryTree */
$queryTree = Tree::find()
    ->where(
        [
            Tree::ATTR_ACCESS_DOMAIN => [
                \Yii::$app->language,
                (Yii::$app->user->can(Tree::GLOBAL_ACCESS_PERMISSION) ? Tree::GLOBAL_ACCESS_DOMAIN : '')
            ]
        ]
    )
    ->orderBy('root, lft');

/**
 * Render tree view
 */
echo TreeView::widget(
    [
        'query' => $queryTree,
        'isAdmin' => true,
        'softDelete' => false,
        'displayValue' => true,
        'showTooltips' => false,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => 'Nodes'],
        'treeOptions' => ['style' => 'height:auto; min-height:400px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
        'toolbar' => $toolbar
    ]
);
