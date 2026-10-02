<?php

use yii\db\Migration;

class m260823_044805_create_class_session extends Migration
{
    public function up()
    {
        $this->createTable('class_session', [
            'id'               => $this->primaryKey(),
            'teacher_id'       => $this->integer()->notNull(),
            'student_id'       => $this->integer()->null(),
            'group_id'         => $this->integer()->null(),
            'lesson_id'        => $this->integer()->null(),
            'homework_id'      => $this->integer()->null(),
            'exam_id'          => $this->integer()->null(),
            'title'            => $this->string(255)->notNull(),
            'scheduled_at'     => $this->integer()->notNull(),
            'duration_minutes' => $this->integer()->null(),
            'status'           => "ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled'",
            'notes'            => $this->text()->null(),
            'created_at'       => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_class_session_teacher', 'class_session', 'teacher_id');
        $this->createIndex('idx_class_session_student', 'class_session', 'student_id');
        $this->createIndex('idx_class_session_group', 'class_session', 'group_id');
        $this->createIndex('idx_class_session_scheduled', 'class_session', 'scheduled_at');

        $this->addForeignKey('fk_cs_teacher', 'class_session', 'teacher_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_cs_student', 'class_session', 'student_id', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_cs_group', 'class_session', 'group_id', 'group', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_cs_lesson', 'class_session', 'lesson_id', 'lesson', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_cs_homework', 'class_session', 'homework_id', 'homework', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_cs_exam', 'class_session', 'exam_id', 'exam', 'id', 'SET NULL', 'CASCADE');

        // Новые типы уведомлений
        $this->execute("
            ALTER TABLE notification
            MODIFY COLUMN type ENUM(
                'homework_assigned','homework_submitted','homework_reviewed',
                'deadline_reminder','exam_assigned','exam_finished','result_ready',
                'lesson_published','lead_received','student_assigned','session_scheduled'
            ) NOT NULL
        ");

        // Токен для просмотра таймлайна родителем без входа
        $this->addColumn('user', 'parent_share_token', $this->string(64)->null()->unique()->after('display_name'));
    }

    public function down()
    {
        $this->dropColumn('user', 'parent_share_token');
        $this->dropTable('class_session');
    }
}
