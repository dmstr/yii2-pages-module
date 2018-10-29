<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\interfaces;


/**
 * Class PageTreeRouteItems
 * @package dmstr\modules\pages\interfaces
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
interface PageTreeRouteItems
{
    /**
     * Resolves route and returns JSON schema
     *
     * @param $route
     * @return string|bool
     */
    public function getPageTreeRequestParamJson($route);
}