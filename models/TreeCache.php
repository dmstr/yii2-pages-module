<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace dmstr\modules\pages\models;


/**
 * @package dmstr\modules\pages\models
 */
class TreeCache
{

    /**
     * @var bool self() Instance of this singleton
     */
    private static $instance = false;

    /**
     * @var array of path strings
     */
    public $path = [];

    /**
     * @return TreeCache
     */
    public static function getInstance()
    {
        # check if we already have an instance, if not init one
        if (self::$instance === false) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}