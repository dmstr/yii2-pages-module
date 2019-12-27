<?php
/**
 * Output TreeView widget
 *
 * @var $this yii\web\View
 * @var $queryTree ActiveQuery
 */
use kartik\tree\TreeView;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;

$this->title = Inflector::titleize($this->context->module->id);

/**
 * Wrapper templates
 */
$headerTemplate = <<< HTML
<div class="row">
    <div class="col-sm-6" id="dmstr-pages-detail-heading">
        {heading}
    </div>
    <div class="col-sm-6" id="dmstr-pages-detail-search">
        {search}
    </div>
</div>
HTML;


/**
 * Additional toolbar elements
 */
$toolbar = [];

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

    if ($settingPermission) {
        $settings = [
            'icon' => 'cogs',
            'url' => ['/settings', 'SettingSearch' => ['section' => 'pages']],
            'options' => [
                'title' => Yii::t('pages', 'Settings'),
                'class' => 'btn btn-info'
            ]
        ];
        $toolbar[] = TreeView::BTN_SEPARATOR;
        $toolbar['settings'] = $settings;
    }
}

$mainTemplate = <<< HTML
<div class="row">
    <div class="col-md-5" id="dmstr-pages-detail-wrapper">
        <div class="box box-solid">
        {wrapper}
        </div>
    </div>
    <div class="col-md-7" id="dmstr-pages-detail-panel">
        {detail}
    </div>
</div>
HTML;



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
