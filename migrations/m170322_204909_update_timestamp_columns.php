<?php

use yii\db\Schema;
use yii\db\Migration;

class m170322_204909_update_timestamp_columns extends Migration
{
    public function up()
    {
        $this->alterColumn('dmstr_page', 'created_at', $this->dateTime());
        $this->alterColumn('dmstr_page', 'updated_at', $this->dateTime());
    }

    public function down()
    {
        echo "m170322_204909_update_timestamp_columns cannot be reverted.\n";

        return false;
    }
}
