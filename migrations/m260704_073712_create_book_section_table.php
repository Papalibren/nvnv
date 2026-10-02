<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book_section}}`.
 */
class m260704_073712_create_book_section_table extends Migration
{
    public function up()
    {
        $this->createTable('book_section', [
            'id'          => $this->primaryKey(),
            'title'       => $this->string(255)->notNull(),
            'slug'        => $this->string(255)->notNull(),
            'description' => $this->string(500)->null(),
            'icon'        => $this->string(50)->null(), // название иконки для карточки
            'sort_order'  => $this->integer()->notNull()->defaultValue(0),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_book_section_slug', 'book_section', 'slug', true);

        // Добавляем связь главы с разделом
        $this->addColumn('book_chapter', 'section_id',
            $this->integer()->null()->after('id'));

        $this->addForeignKey(
            'fk_book_chapter_section',
            'book_chapter', 'section_id',
            'book_section', 'id',
            'CASCADE', 'CASCADE'
        );

        // Создаём дефолтный раздел и привязываем существующие главы
        $this->insert('book_section', [
            'title'      => 'Информатика',
            'slug'       => 'informatika',
            'sort_order' => 0,
            'created_at' => time(),
        ]);

        $defaultSectionId = $this->db->getLastInsertID();
        $this->update('book_chapter', ['section_id' => $defaultSectionId]);
    }

    public function down()
    {
        $this->dropForeignKey('fk_book_chapter_section', 'book_chapter');
        $this->dropColumn('book_chapter', 'section_id');
        $this->dropTable('book_section');
    }
}
