<?php

use yii\db\Migration;

class m260710_051533_create_rating_system extends Migration
{
    public function up()
    {
        // Настройки весов — синглтон, одна строка
        $this->createTable('rating_weight_config', [
            'id'                  => $this->primaryKey(),
            'course_weight'       => $this->integer()->notNull()->defaultValue(28),
            'tutoring_weight'     => $this->integer()->notNull()->defaultValue(12),
            'public_task_weight'  => $this->integer()->notNull()->defaultValue(32),
            'public_exam_weight'  => $this->integer()->notNull()->defaultValue(28),
            'updated_at'          => $this->integer()->notNull(),
        ]);

        $this->insert('rating_weight_config', [
            'course_weight'      => 28,
            'tutoring_weight'    => 12,
            'public_task_weight' => 32,
            'public_exam_weight' => 28,
            'updated_at'         => time(),
        ]);

        // Публичные задачи для рейтинга — открываются периодически, доступны всем
        $this->createTable('public_challenge', [
            'id'          => $this->primaryKey(),
            'task_id'     => $this->integer()->notNull(),
            'title'       => $this->string(255)->notNull(),
            'points'      => $this->integer()->notNull()->defaultValue(20),
            'opens_at'    => $this->integer()->notNull(),
            'closes_at'   => $this->integer()->notNull(),
            'created_by'  => $this->integer()->notNull(),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_public_challenge_task', 'public_challenge', 'task_id', 'task', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_public_challenge_creator', 'public_challenge', 'created_by', 'user', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('public_challenge_attempt', [
            'id'            => $this->primaryKey(),
            'challenge_id'  => $this->integer()->notNull(),
            'student_id'    => $this->integer()->notNull(),
            'answer_text'   => $this->string(500)->notNull()->defaultValue(''),
            'is_correct'    => $this->boolean()->notNull()->defaultValue(false),
            'points_earned' => $this->integer()->notNull()->defaultValue(0),
            'submitted_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_challenge_attempt_unique', 'public_challenge_attempt', ['challenge_id', 'student_id'], true);

        $this->addForeignKey('fk_challenge_attempt_challenge', 'public_challenge_attempt', 'challenge_id', 'public_challenge', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_challenge_attempt_student', 'public_challenge_attempt', 'student_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('public_challenge_attempt');
        $this->dropTable('public_challenge');
        $this->dropTable('rating_weight_config');
    }
}
