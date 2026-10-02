<?php

use yii\db\Migration;

class m260626_080047_create_theory_table extends Migration
{
    public function up()
    {
        $this->createTable('theory_topic', [
            'id'           => $this->primaryKey(),
            'task_number'  => $this->smallInteger()->notNull(),
            'title'        => $this->string(255)->notNull(),
            'slug'         => $this->string(255)->notNull(),
            'content'      => $this->text()->notNull(),
            'book_page_id' => $this->integer()->null()->defaultValue(null),
            'published_at' => $this->integer()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_theory_topic_slug',        'theory_topic', 'slug', true);
        $this->createIndex('idx_theory_topic_task_number', 'theory_topic', 'task_number');

        $this->addForeignKey(
            'fk_theory_topic_book_page',
            'theory_topic', 'book_page_id',
            'book_page', 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('theory_topic');
    }
}