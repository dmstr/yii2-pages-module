<?php

namespace dmstr\modules\pages\assets;

use yii\web\AssetBundle;

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
    public $sourcePath = '@vendor/dmstr/yii2-pages-module/assets';
    public $css = [
        'less/backend.less',
    ];
    public $js = [
        'js/page-route-items.js'
    ];
    public $depends = [
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
