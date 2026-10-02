<?php

use yii\db\Migration;

class m260626_080247_create_homework_tables extends Migration
{
    public function up()
    {
        $this->createTable('homework', [
            'id'          => $this->primaryKey(),
            'teacher_id'  => $this->integer()->notNull(),
            'group_id'    => $this->integer()->null()->defaultValue(null),
            'lesson_id'   => $this->integer()->null()->defaultValue(null),
            'title'       => $this->string(255)->notNull(),
            'deadline_at' => $this->integer()->null()->defaultValue(null),
            'status'      => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_homework_teacher',
            'homework', 'teacher_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_homework_group',
            'homework', 'group_id', 'group', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_homework_lesson',
            'homework', 'lesson_id', 'lesson', 'id', 'SET NULL', 'CASCADE');

        // Задачи в ДЗ
        $this->createTable('homework_task', [
            'id'          => $this->primaryKey(),
            'homework_id' => $this->integer()->notNull(),
            'task_id'     => $this->integer()->notNull(),
            'max_points'  => $this->smallInteger()->notNull()->defaultValue(10),
            'sort_order'  => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('idx_homework_task_unique',
            'homework_task', ['homework_id', 'task_id'], true);

        $this->addForeignKey('fk_homework_task_hw',
            'homework_task', 'homework_id', 'homework', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_homework_task_task',
            'homework_task', 'task_id', 'task', 'id', 'RESTRICT', 'CASCADE');

        // Выдача ДЗ ученику (fan-out)
        $this->createTable('homework_student', [
            'id'          => $this->primaryKey(),
            'homework_id' => $this->integer()->notNull(),
            'student_id'  => $this->integer()->notNull(),
            'status'      => "ENUM('assigned','in_progress','submitted','reviewed')
                              NOT NULL DEFAULT 'assigned'",
            'assigned_at' => $this->integer()->notNull(),
            'submitted_at'=> $this->integer()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_homework_student_unique',
            'homework_student', ['homework_id', 'student_id'], true);

        $this->addForeignKey('fk_homework_student_hw',
            'homework_student', 'homework_id', 'homework', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_homework_student_student',
            'homework_student', 'student_id', 'user', 'id', 'CASCADE', 'CASCADE');

        // Ответы ученика
        $this->createTable('homework_answer', [
            'id'                 => $this->primaryKey(),
            'homework_student_id'=> $this->integer()->notNull(),
            'homework_task_id'   => $this->integer()->notNull(),
            'answer_text'        => $this->text()->notNull(),
            'is_correct'         => $this->boolean()->null()->defaultValue(null),
            'points_earned'      => $this->smallInteger()->null()->defaultValue(null),
            'attempt_number'     => $this->smallInteger()->notNull()->defaultValue(1),
            'submitted_at'       => $this->integer()->null()->defaultValue(null),
            'file_path'          => $this->string(500)->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_homework_answer_unique',
            'homework_answer',
            ['homework_student_id', 'homework_task_id', 'attempt_number'],
            true
        );

        $this->addForeignKey('fk_homework_answer_hs',
            'homework_answer', 'homework_student_id',
            'homework_student', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_homework_answer_ht',
            'homework_answer', 'homework_task_id',
            'homework_task', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('homework_answer');
        $this->dropTable('homework_student');
        $this->dropTable('homework_task');
        $this->dropTable('homework');
    }
}