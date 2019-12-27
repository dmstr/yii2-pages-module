<?php

use yii\db\Migration;

/**
 * Class m190325_133525_remove_not_needed_columns
 */
class m190325_133525_remove_not_needed_columns extends Migration
{

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->dropColumn('{{%dmstr_page_translation}}', 'page_title');
        $this->dropColumn('{{%dmstr_page_translation}}', 'default_meta_keywords');
        $this->dropColumn('{{%dmstr_page_translation}}', 'default_meta_description');

        $this->dropColumn('{{%dmstr_page}}', 'slug');
        $this->dropColumn('{{%dmstr_page}}', 'view');
    }

    /**
     * @return bool
     */
    public function down()
    {
        echo "m190325_133525_remove_not_needed_columns cannot be reverted.\n";
        return false;
    }
}
