<?php

use yii\db\Migration;

class m150623_164544_auth_items extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        if ($auth) {
            $permission = $auth->createPermission('pages');
            $permission->description = 'Pages Module';
            $auth->add($permission);
        }
    }

    public function down()
    {
        echo "m150623_164544_auth_items cannot be reverted.\n";

        return false;
    }
}
