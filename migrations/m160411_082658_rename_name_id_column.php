<?php

use yii\db\Migration;

class m160411_082658_rename_name_id_column extends Migration
{
    public function up()
    {
        $this->dropIndex('name_id_UNIQUE', 'dmstr_page');
        $this->renameColumn('dmstr_page', 'name_id', 'domain_id');
        if ($this->db->driverName === 'pgsql') {
            $this->execute('ALTER TABLE dmstr_page ALTER COLUMN domain_id SET NOT NULL;');
            $this->execute(
                'CREATE UNIQUE INDEX name_id_UNIQUE ON dmstr_page (domain_id, access_domain);'
            );            
        } else {
            $this->execute('ALTER TABLE dmstr_page MODIFY COLUMN domain_id VARCHAR(255) NOT NULL;');
            $this->execute(
                'ALTER TABLE `dmstr_page` ADD UNIQUE INDEX `name_id_UNIQUE` (`domain_id`, `access_domain`);'
            );
        }
    }

    public function down()
    {
        return false;
    }
}
