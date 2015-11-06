<?php

namespace dmstr\modules\pages\assets;

use yii\web\AssetBundle;

/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PagesAsset extends AssetBundle
{
    public $sourcePath;
    public $css = [
        'module.less'
    ];
    
    public function init() {
        parent::init();
        $this->sourcePath = __DIR__.'/less';
    }

}
