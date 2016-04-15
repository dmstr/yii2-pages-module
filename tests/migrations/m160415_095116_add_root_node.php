<?php

use yii\db\Schema;
use yii\db\Migration;

class m160415_095116_add_root_node extends Migration
{
    public function up()
    {
        $this->execute(
            "
INSERT INTO `dmstr_page` (`id`, `root`, `lft`, `rgt`, `lvl`, `page_title`, `name`, `domain_id`, `slug`, `route`, `view`, `default_meta_keywords`, `default_meta_description`, `request_params`, `owner`, `access_owner`, `access_domain`, `access_read`, `access_update`, `access_delete`, `icon`, `icon_type`, `active`, `selected`, `disabled`, `readonly`, `visible`, `collapsed`, `movable_u`, `movable_d`, `movable_l`, `movable_r`, `removable`, `removable_all`, `created_at`, `updated_at`)
VALUES
	(1, 1, 1, 2, 0, '', 'Startseite', 'root', NULL, '', '', '', '', '{}', NULL, NULL, 'de', NULL, NULL, NULL, '', 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, '2016-04-15 09:53:03', '2016-04-15 09:53:03');

"
        );
    }

    public function down()
    {
        echo "m160415_095116_add_root_node cannot be reverted.\n";

        return false;
    }
}
