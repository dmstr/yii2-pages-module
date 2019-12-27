<?php

namespace dmstr\modules\pages\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PagesBackendAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/backend/web';
    public $css = [
        'less/backend.less',
    ];
    public $js = [
        'js/page-route-items.js',
        'js/page-select.js'
    ];
    public $depends = [
        JqueryAsset::class,
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
