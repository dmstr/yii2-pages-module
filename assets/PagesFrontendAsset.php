<?php
/**
 * Created by PhpStorm.
 * User: schmunk
 * Date: 24.07.18
 * Time: 21:18
 */

namespace dmstr\modules\pages\assets;


use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class PagesFrontendAsset extends AssetBundle
{
    public $sourcePath = '@vendor/dmstr/yii2-pages-module/assets/less';
    public $css = [
        'frontend.less',
    ];
    public $js = [
        'frontend.js',
    ];
    public $depends = [
        JqueryAsset::class
    ];
}