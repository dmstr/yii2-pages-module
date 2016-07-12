<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use yii\db\Migration;

class m150309_153255_create_tree_manager_table extends Migration
{
    public function up()
    {
        $this->createTable(
            'dmstr_page',
            [
                'id' => $this->primaryKey(),
                'root' => $this->integer()->notNull()->defaultValue(0),
                'lft' => $this->integer()->notNull(),
                'rgt' => $this->integer()->notNull(),
                'lvl' => $this->smallInteger()->notNull(),
                'page_title' => $this->string(255),
                'name' => $this->string(60)->notNull(),
                'name_id' => $this->string(255)->notNull(),
                'slug' => $this->string(255),
                'route' => $this->string(255),
                'view' => $this->string(255),
                'default_meta_keywords' => $this->string(255),
                'default_meta_description' => $this->text(),
                'request_params' => $this->text(),
                'owner' => $this->integer()->defaultValue(null),
                'access_owner' => $this->integer()->defaultValue(null),
                'access_domain' => $this->string(8)->defaultValue(null),
                'access_read' => $this->string(255)->defaultValue(null),
                'access_update' => $this->string(255)->defaultValue(null),
                'access_delete' => $this->string(255)->defaultValue(null),
                'icon' => $this->string(255)->defaultValue(null),
                'icon_type' => $this->smallInteger()->defaultValue(1),
                'active' => $this->smallInteger()->defaultValue(1),
                'selected' => $this->smallInteger()->defaultValue(0),
                'disabled' => $this->smallInteger()->defaultValue(0),
                'readonly' => $this->smallInteger()->defaultValue(0),
                'visible' => $this->smallInteger()->defaultValue(1),
                'collapsed' => $this->smallInteger()->defaultValue(0),
                'movable_u' => $this->smallInteger()->defaultValue(1),
                'movable_d' => $this->smallInteger()->defaultValue(1),
                'movable_l' => $this->smallInteger()->defaultValue(1),
                'movable_r' => $this->smallInteger()->defaultValue(1),
                'removable' => $this->smallInteger()->defaultValue(1),
                'removable_all' => $this->smallInteger()->defaultValue(0),
                'created_at' => $this->timestamp()->defaultExpression('NOW()'),
                'updated_at' => $this->timestamp()->defaultExpression('NOW()'),
            ]
        );

        $this->createIndex('tbl_tree_NK1', 'dmstr_page', 'root');
        $this->createIndex('tbl_tree_NK2', 'dmstr_page', 'lft');
        $this->createIndex('tbl_tree_NK3', 'dmstr_page', 'rgt');
        $this->createIndex('tbl_tree_NK4', 'dmstr_page', 'lvl');
        $this->createIndex('tbl_tree_NK5', 'dmstr_page', 'active');

        $this->createIndex('name_id_UNIQUE', 'dmstr_page', 'name_id', true);
    }

    public function down()
    {
        $this->dropTable('dmstr_page');
    }
}
