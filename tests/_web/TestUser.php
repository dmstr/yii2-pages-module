<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2016 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\tests\_web;

use yii\web\User;

class TestUser extends User
{
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        return true;
    }
}