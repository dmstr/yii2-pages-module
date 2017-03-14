<?php

use yii\db\Migration;

class m170314_111404_remove_owner_column extends Migration
{
    public function up()
    {
        $this->dropColumn('dmstr_page', 'owner');
    }

    public function down()
    {
        return false;
    }
}
