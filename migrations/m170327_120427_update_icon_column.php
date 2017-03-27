<?php

use yii\db\Schema;
use yii\db\Migration;

class m170327_120427_update_icon_column extends Migration
{
    public function up()
    {
        $this->update('dmstr_page', ['icon' => new \yii\db\Expression("REPLACE(icon, 'fa fa-', '')")]);
    }

    public function down()
    {
        echo "m170327_120427_update_icon_column cannot be reverted.\n";

        return false;
    }
}
