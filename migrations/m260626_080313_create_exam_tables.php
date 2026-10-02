<?php

use yii\db\Migration;

class m260626_080313_create_exam_tables extends Migration
{
    public function up()
    {
        $this->createTable('exam', [
            'id'               => $this->primaryKey(),
            'teacher_id'       => $this->integer()->notNull(),
            'group_id'         => $this->integer()->null(),
            'title'            => $this->string(255)->notNull(),
            'duration_minutes' => $this->smallInteger()->null(),
            'is_full_scored'   => $this->boolean()->notNull()->defaultValue(true),
            'status'           => "ENUM('draft','published','finished') NOT NULL DEFAULT 'draft'",
            'created_at'       => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_exam_teacher',
            'exam', 'teacher_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_exam_group',
            'exam', 'group_id', 'group', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('exam_task', [
            'id'         => $this->primaryKey(),
            'exam_id'    => $this->integer()->notNull(),
            'task_id'    => $this->integer()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('idx_exam_task_unique', 'exam_task', ['exam_id', 'task_id'], true);

        $this->addForeignKey('fk_exam_task_exam',
            'exam_task', 'exam_id', 'exam', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_exam_task_task',
            'exam_task', 'task_id', 'task', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('exam_attempt', [
            'id'           => $this->primaryKey(),
            'exam_id'      => $this->integer()->notNull(),
            'student_id'   => $this->integer()->notNull(),
            'started_at'   => $this->integer()->notNull(),
            'submitted_at' => $this->integer()->null(),
            'status'       => "ENUM('in_progress','submitted','expired') NOT NULL DEFAULT 'in_progress'",
            'score_total'  => $this->smallInteger()->null(),
            'score_max'    => $this->smallInteger()->null(),
        ]);

        $this->createIndex('idx_exam_attempt_unique',
            'exam_attempt', ['exam_id', 'student_id'], true);

        $this->addForeignKey('fk_exam_attempt_exam',
            'exam_attempt', 'exam_id', 'exam', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_exam_attempt_student',
            'exam_attempt', 'student_id', 'user', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('exam_attempt_answer', [
            'id'                      => $this->primaryKey(),
            'attempt_id'              => $this->integer()->notNull(),
            'task_id'                 => $this->integer()->notNull(),
            'student_answer'          => $this->text()->notNull(),
            'correct_answer_snapshot' => $this->string(500)->notNull()->defaultValue(''),
            'is_correct'              => $this->boolean()->null(),
            'points_earned'           => $this->smallInteger()->null(),
            'answered_at'             => $this->integer()->null(),
        ]);

        $this->createIndex('idx_exam_attempt_answer_unique',
            'exam_attempt_answer', ['attempt_id', 'task_id'], true);

        $this->addForeignKey('fk_exam_attempt_answer_attempt',
            'exam_attempt_answer', 'attempt_id',
            'exam_attempt', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_exam_attempt_answer_task',
            'exam_attempt_answer', 'task_id',
            'task', 'id', 'RESTRICT', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('exam_attempt_answer');
        $this->dropTable('exam_attempt');
        $this->dropTable('exam_task');
        $this->dropTable('exam');
    }
}