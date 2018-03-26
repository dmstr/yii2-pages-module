<?php

use yii\db\Migration;

/**
 * Class m180321_090927_add_translation_table
 */
class m180321_090927_add_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable(
            'dmstr_page_translation',
            [
                'id' => $this->primaryKey(),
                'page_id' => $this->integer()->notNull(),
                'language' => $this->char(7)->notNull(),
                'name' => $this->string(60)->notNull(),
                'page_title' => $this->string(255),
                'default_meta_keywords' => $this->string(255),
                'default_meta_description' => $this->text(),
                'created_at' => $this->timestamp()->defaultExpression('NOW()'),
                'updated_at' => $this->timestamp()->defaultExpression('NOW()'),
            ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
        );

        $this->addForeignKey('FK_page_translation_page', 'dmstr_page_translation','page_id','dmstr_page','id');

        // select all pages to insert them into the translation table
        $query = new \yii\db\Query();
        $pages = $query->select([
            'id',
            'name',
            'page_title',
            'access_domain',
            'default_meta_keywords',
            'default_meta_description',
        ])->from('dmstr_page')->all();

        // insert them into translation table
        foreach ($pages as $page) {
            // if access domain is global iterate over all configured languages to add a translation for every single one
            if ($page['access_domain'] === '*') {
                foreach (\Yii::$app->urlManager->languages as $language) {
                    $this->insertPageTranslation($page,$language);
                }
            } else {
                $this->insertPageTranslation($page,$page['access_domain']);
            }
            
        }


        $this->dropColumn('dmstr_page', 'name');
        $this->dropColumn('dmstr_page', 'page_title');
        $this->dropColumn('dmstr_page', 'default_meta_keywords');
        $this->dropColumn('dmstr_page', 'default_meta_description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180321_090927_add_translation_table cannot be reverted.\n";
        return false;
    }
    
    private function insertPageTranslation($page, $language) {
        $this->insert('dmstr_page_translation', [
            'page_id' => $page['id'],
            'language' => $language,
            'name' => $page['name'],
            'page_title' => $page['page_title'],
            'default_meta_keywords' => $page['default_meta_keywords'],
            'default_meta_description' => $page['default_meta_description'],
        ]);
    }

}
