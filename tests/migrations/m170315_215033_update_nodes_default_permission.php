<?php

use yii\db\Schema;
use yii\db\Migration;

class m170315_215033_update_nodes_default_permission extends Migration
{
    public function up()
    {
        $this->update('dmstr_page', ['access_read' => '*', 'access_update' => '*', 'access_delete' => '*'], ['id' => 1]);
        $this->update('dmstr_page', ['access_read' => '*', 'access_update' => '*', 'access_delete' => '*'], ['id' => 2]);
    }

    public function down()
    {
        echo "m170315_215033_update_nodes_default_permission cannot be reverted.\n";

        return false;
    }
}
