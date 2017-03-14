<?php

use yii\db\Migration;

class m170314_062644_set_default_access_read extends Migration
{
    public function up()
    {
        $this->update('dmstr_page', ['access_read' => '*']);
    }

    public function down()
    {
        $this->update('dmstr_page', ['access_read' => null]);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
