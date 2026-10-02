<?php

use yii\db\Migration;

class m260626_080137_create_task_tables extends Migration
{
    public function up()
    {
        $this->createTable('task', [
            'id'                 => $this->primaryKey(),
            'task_number'        => $this->smallInteger()->notNull(),
            'title'              => $this->string(255)->null(),
            'content'            => $this->text()->notNull(),
            'answer'             => $this->string(500)->notNull()->defaultValue(''),
            'solution_content'   => $this->text()->null(),
            'solution_is_public' => $this->boolean()->notNull()->defaultValue(false),
            'difficulty'         => $this->smallInteger()->notNull()->defaultValue(5),
            'has_file'           => $this->boolean()->notNull()->defaultValue(false),
            'status'             => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            'created_by'         => $this->integer()->notNull(),
            'created_at'         => $this->integer()->notNull(),
            'updated_at'         => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_task_task_number', 'task', 'task_number');
        $this->createIndex('idx_task_status',      'task', 'status');
        $this->createIndex('idx_task_difficulty',  'task', 'difficulty');

        $this->addForeignKey(
            'fk_task_created_by',
            'task', 'created_by',
            'user', 'id',
            'RESTRICT', 'CASCADE'
        );

        $this->createTable('task_file', [
            'id'        => $this->primaryKey(),
            'task_id'   => $this->integer()->notNull(),
            'filename'  => $this->string(255)->notNull(),
            'path'      => $this->string(500)->notNull(),
            'mime_type' => $this->string(100)->notNull()->defaultValue(''),
            'size'      => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk_task_file_task',
            'task_file', 'task_id',
            'task', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable('task_tag', [
            'id'   => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'slug' => $this->string(100)->notNull(),
        ]);

        $this->createIndex('idx_task_tag_slug', 'task_tag', 'slug', true);

        $this->createTable('task_tag_pivot', [
            'task_id' => $this->integer()->notNull(),
            'tag_id'  => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_task_tag_pivot', 'task_tag_pivot', ['task_id', 'tag_id']);

        $this->addForeignKey(
            'fk_task_tag_pivot_task',
            'task_tag_pivot', 'task_id',
            'task', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_task_tag_pivot_tag',
            'task_tag_pivot', 'tag_id',
            'task_tag', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable('task_theory_link', [
            'task_id'         => $this->integer()->notNull(),
            'theory_topic_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk_task_theory_link',
            'task_theory_link',
            ['task_id', 'theory_topic_id']
        );

        $this->addForeignKey(
            'fk_task_theory_link_task',
            'task_theory_link', 'task_id',
            'task', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_task_theory_link_theory',
            'task_theory_link', 'theory_topic_id',
            'theory_topic', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('task_theory_link');
        $this->dropTable('task_tag_pivot');
        $this->dropTable('task_tag');
        $this->dropTable('task_file');
        $this->dropTable('task');
    }
}