<?php

use yii\db\Schema;
use yii\db\Migration;

class m170315_221005_set_default_update_and_delete_access extends Migration
{
    public function up()
    {
        $this->update('dmstr_page', ['access_update' => '*']);
        $this->update('dmstr_page', ['access_delete' => '*']);
    }

    public function down()
    {
        $this->update('dmstr_page', ['access_update' => null]);
        $this->update('dmstr_page', ['access_delete' => null]);
    }
}
