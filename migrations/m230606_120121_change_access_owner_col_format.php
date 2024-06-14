<?php

use yii\db\Migration;

class m230606_120121_change_access_owner_col_format extends Migration
{
    public function up()
    {
        // allow uuid and longer strings as access_owner value
        $this->alterColumn("{{%dmstr_page}}", "access_owner", $this->string(255));
    }

    public function down()
    {
        return false;
    }
}
