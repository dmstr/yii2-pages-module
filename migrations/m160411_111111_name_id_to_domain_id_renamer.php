<?php

use yii\db\Migration;

class m160411_111111_name_id_to_domain_id_renamer extends Migration
{
    public function up()
    {
        $languages = explode(',', getenv('APP_LANGUAGES'));

        if (count($languages) > 0 && $languages[0] !== '') {
            foreach ($languages as $language) {

                $this->execute(
                    "
                UPDATE dmstr_page SET domain_id = REPLACE (domain_id, '_$language', '') WHERE domain_id LIKE '%_$language';
                "
                );
            }
            return true;
        }
        return false;
    }

    public function down()
    {
        return false;
    }
}
