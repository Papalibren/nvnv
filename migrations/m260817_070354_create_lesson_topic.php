<?php

use yii\db\Migration;

class m260817_070354_create_lesson_topic extends Migration
{
    public function up()
    {
        $this->createTable('lesson_topic', [
            'id'          => $this->primaryKey(),
            'title'       => $this->string(255)->notNull(),
            'slug'        => $this->string(255)->notNull(),
            'description' => $this->string(500)->null(),
            'sort_order'  => $this->integer()->notNull()->defaultValue(0),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_lesson_topic_slug', 'lesson_topic', 'slug', true);

        $this->addColumn('lesson', 'topic_id', $this->integer()->null()->after('teacher_id'));

        $this->addForeignKey(
            'fk_lesson_topic',
            'lesson', 'topic_id',
            'lesson_topic', 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_lesson_topic', 'lesson');
        $this->dropColumn('lesson', 'topic_id');
        $this->dropTable('lesson_topic');
    }
}
