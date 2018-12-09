<?php

use yii\db\Migration;

/**
 * Class m180702_153622_add_translation_meta_table
 */
class m180702_153622_add_translation_meta_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dmstr_page_translation_meta}}', [
            'id' => $this->primaryKey(),
            'page_id' => $this->integer()->notNull(),
            'language' => $this->char(7)->notNull(),
            'disabled' => $this->smallInteger()->defaultValue(0),
            'visible' => $this->smallInteger()->defaultValue(1),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk_page_page_translation_meta_id',
            '{{%dmstr_page_translation_meta}}',
            'page_id',
            '{{%dmstr_page}}',
            'id',
            'CASCADE',
            'CASCADE');


        // select all contents to insert them into the translation table
        $query = new \yii\db\Query();
        $pages = $query->select([
            'id',
            'disabled',
            'visible',
        ])->from('{{%dmstr_page}}')->all();

        foreach ($pages as $page) {
            $this->insert('{{%dmstr_page_translation_meta}}', [
                'page_id' => $page['id'],
                'language' => Yii::$app->language,
                'disabled' => $page['disabled'],
                'visible' => $page['visible'],
            ]);
        }

        $this->dropColumn('{{%dmstr_page}}', 'disabled');
        $this->dropColumn('{{%dmstr_page}}', 'visible');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%dmstr_page}}', 'disabled',
            $this->smallInteger()->defaultValue(0)->after('selected'));
        $this->addColumn('{{%dmstr_page}}', 'visible',
            $this->smallInteger()->defaultValue(1)->after('readonly'));

        // select all content translations to insert them back into the content table
        $query = new \yii\db\Query();
        $pages = $query->select([
            'page_id',
            'language',
            'disabled',
            'visible'
        ])->from('{{%dmstr_page_translation_meta}}')->all();

        foreach ($pages as $page) {
            $this->update('{{%dmstr_page}}', [
                'disabled' => $page['disabled'],
                'visible' => $page['visible']
            ],['id' => $page['page_id']]);
        }

        $this->dropForeignKey('fk_page_page_translation_meta_id', '{{%dmstr_page_translation_meta}}');
        $this->dropTable('{{%dmstr_page_translation_meta}}');
    }

}
