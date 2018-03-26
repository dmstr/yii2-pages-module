<?php

use yii\db\Migration;

/**
 * Class m180321_103245_alter_table_names
 */
class m180321_103245_alter_table_names extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->renameTable('dmstr_page','{{%dmstr_page}}');
        $this->renameTable('dmstr_page_translation','{{%dmstr_page_translation}}');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180321_103245_alter_table_names cannot be reverted.\n";
        return false;
    }


}
