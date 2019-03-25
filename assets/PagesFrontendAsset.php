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

/**
 * @package dmstr\modules\pages\assets
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
class PagesFrontendAsset extends AssetBundle
{
    public $sourcePath = __DIR__.'/frontend/web';
    public $css = [
        'less/frontend.less',
    ];
    public $js = [
        'js/frontend.js',
    ];
    public $depends = [
        JqueryAsset::class
    ];
}