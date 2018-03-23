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

        $this->addForeignKey('FK_page_translation_page', 'dmstr_page_translation','page_id','dmstr_page','id','CASCADE','CASCADE');

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

        $this->addColumn('dmstr_page','name','VARCHAR(60) NOT NULL AFTER id');
        $this->addColumn('dmstr_page','page_title','VARCHAR(255) NULL AFTER name');
        $this->addColumn('dmstr_page','default_meta_keywords','VARCHAR(255) NULL AFTER page_title');
        $this->addColumn('dmstr_page','default_meta_description','TEXT(2048) NULL AFTER default_meta_keywords');

        // transfer translations back into pages table
        $query = new \yii\db\Query();
        $pages = $query->select([
            'page_id',
            'language',
            'name',
            'page_title',
            'default_meta_keywords',
            'default_meta_description',
            'SUM(1)'
        ])->from('dmstr_page_translation')->groupBy('page_id')->all();

        foreach ($pages as $page) {
            // check if sum of pages grouped by page id is equal to sum of configured languages. If so set it to global
            $language = (int)$page['SUM(1)'] === count(\Yii::$app->urlManager->languages) ? '*' : $page['language'];

            $this->update('dmstr_page', [
                'name' => $page['name'],
                'page_title' => $page['page_title'],
                'default_meta_keywords' => $page['default_meta_keywords'],
                'default_meta_description' => $page['default_meta_description'],
            ], 'id = ' . $page['page_id'] . ' AND access_domain = "' . $language . '"');
        }

        $this->dropForeignKey('FK_page_translation_page', 'dmstr_page_translation');
        $this->dropTable('dmstr_page_translation');
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
