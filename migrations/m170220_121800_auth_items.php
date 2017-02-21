<?php

use yii\db\Migration;

class m170220_121800_auth_items extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        if ($auth) {
            $permission = $auth->createPermission('pages_copy');
            $permission->description = 'Pages Copy';
            $auth->add($permission);
        }
    }

    public function down()
    {
        echo "m170220_121800_auth_items cannot be reverted.\n";

        return false;
    }
}
