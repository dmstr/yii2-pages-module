<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\models;

use yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;

/**
 * Class Menu
 * @package dmstr\modules\pages\models
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class Menu extends ActiveRecord
{
    /**
     * Menu behaviors
     * @return array
     */
    public function behaviors() {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                // 'treeAttribute' => 'tree',
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ],
        ];
    }

    /**
     * Transactions
     * @return array
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * Find using MenuQuery class
     * @return MenuQuery
     */
    public static function find()
    {
        return new MenuQuery(get_called_class());
    }
}