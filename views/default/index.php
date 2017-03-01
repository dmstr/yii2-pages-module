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
 * Links to settings and copy pages area for toolbar
 */
$copyPages = [
    'icon' => 'copy',
    'url' => (\Yii::$app->user->can(Tree::COPY_ACCESS_PERMISSION)) ? ['/pages/copy'] : null,
    'options' => [
        'title' => Yii::t('pages', 'Copy root nodes'),
        'disabled' => (\Yii::$app->user->can(Tree::COPY_ACCESS_PERMISSION) ? false : true),
        'class' => 'btn btn-success'
    ],
];
// check settings component and module existence
if (\Yii::$app->has('settings') && \Yii::$app->hasModule('settings')) {

    // check module permissions
    $settingPermission = false;
    if (\Yii::$app->getModule('settings')->accessRoles === null) {
        $settingPermission = true;
    } else {
        foreach (\Yii::$app->getModule('settings')->accessRoles as $role) {
            $settingPermission = \Yii::$app->user->can($role);
        }
    }


    $settings = [
        'icon' => 'cogs',
        'url' => ['/settings', 'SettingSearch' => ['section' => 'pages']],
        'options' => [
            'title' => Yii::t('pages', 'Settings'),
            'disabled' => ! $settingPermission,
            'class' => 'btn btn-info'
        ]
    ];
}

/**
 * Additional toolbar elements
 */
$toolbar = [
    TreeView::BTN_SEPARATOR,
    TreeView::BTN_SEPARATOR,
    TreeView::BTN_SEPARATOR,
    'copy'     => $copyPages,
    TreeView::BTN_SEPARATOR,
    'settings' => $settings,
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
        'displayValue' => 1,
        'showTooltips' => false,
        'wrapperTemplate' => '{header}{footer}{tree}',
        'headingOptions' => ['label' => 'Nodes'],
        'treeOptions' => ['style' => 'height:auto; min-height:400px'],
        'headerTemplate' => $headerTemplate,
        'mainTemplate' => $mainTemplate,
        'toolbar' => $toolbar
    ]
);
