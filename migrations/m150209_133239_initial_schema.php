<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m150209_133239_initial_schema
 * @package dmstr\modules\pages\migrations
 * @author Christopher Stebe <c.stebe@herzogkommunikation.de>
 */
class m150209_133239_initial_schema extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%menu}}',
            [
                'id'    => Schema::TYPE_PK,
                'tree'  => Schema::TYPE_INTEGER,
                'lft'   => Schema::TYPE_INTEGER . ' NOT NULL',
                'rgt'   => Schema::TYPE_INTEGER . ' NOT NULL',
                'depth' => Schema::TYPE_INTEGER . ' NOT NULL',
                'name'  => Schema::TYPE_STRING . ' NOT NULL',
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%menu}}');
    }
}
