<?php

use yii\db\Migration;

class m260626_080023_create_book_tables extends Migration
{
    public function up()
    {
        $this->createTable('book_chapter', [
            'id'         => $this->primaryKey(),
            'title'      => $this->string(255)->notNull(),
            'slug'       => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'parent_id'  => $this->integer()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_book_chapter_slug', 'book_chapter', 'slug', true);

        $this->addForeignKey(
            'fk_book_chapter_parent',
            'book_chapter', 'parent_id',
            'book_chapter', 'id',
            'SET NULL', 'CASCADE'
        );

        $this->createTable('book_page', [
            'id'           => $this->primaryKey(),
            'chapter_id'   => $this->integer()->notNull(),
            'title'        => $this->string(255)->notNull(),
            'slug'         => $this->string(255)->notNull(),
            'content'      => $this->text()->notNull(),
            'sort_order'   => $this->integer()->notNull()->defaultValue(0),
            'published_at' => $this->integer()->null()->defaultValue(null),
            'updated_at'   => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_book_page_slug',       'book_page', 'slug', true);
        $this->createIndex('idx_book_page_chapter_id', 'book_page', 'chapter_id');

        $this->addForeignKey(
            'fk_book_page_chapter',
            'book_page', 'chapter_id',
            'book_chapter', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('book_page');
        $this->dropTable('book_chapter');
    }
}