<?php

use yii\db\Migration;

class m260626_080225_create_lesson_slide_tables extends Migration
{
    public function up()
    {
        $this->createTable('lesson', [
            'id'           => $this->primaryKey(),
            'teacher_id'   => $this->integer()->notNull(),
            'group_id'     => $this->integer()->null(),
            'title'        => $this->string(255)->notNull(),
            'description'  => $this->text()->null(),
            'scheduled_at' => $this->integer()->null(),
            'status'       => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            'created_at'   => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_lesson_teacher',
            'lesson', 'teacher_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_lesson_group',
            'lesson', 'group_id', 'group', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('lesson_theory_link', [
            'id'           => $this->primaryKey(),
            'lesson_id'    => $this->integer()->notNull(),
            'content_type' => "ENUM('book_page','theory_topic') NOT NULL",
            'content_id'   => $this->integer()->notNull(),
            'sort_order'   => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey('fk_lesson_theory_link_lesson',
            'lesson_theory_link', 'lesson_id', 'lesson', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('slide_deck', [
            'id'         => $this->primaryKey(),
            'lesson_id'  => $this->integer()->null(),
            'teacher_id' => $this->integer()->notNull(),
            'title'      => $this->string(255)->notNull(),
            'status'     => "ENUM('draft','ready') NOT NULL DEFAULT 'draft'",
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_slide_deck_lesson',
            'slide_deck', 'lesson_id', 'lesson', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_slide_deck_teacher',
            'slide_deck', 'teacher_id', 'user', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('slide', [
            'id'         => $this->primaryKey(),
            'deck_id'    => $this->integer()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'content'    => $this->text()->notNull(),
            'notes'      => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_slide_deck_sort', 'slide', ['deck_id', 'sort_order']);

        $this->addForeignKey('fk_slide_deck',
            'slide', 'deck_id', 'slide_deck', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('slide');
        $this->dropTable('slide_deck');
        $this->dropTable('lesson_theory_link');
        $this->dropTable('lesson');
    }
}