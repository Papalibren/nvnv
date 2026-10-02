<?php

use yii\db\Migration;

class m260627_173137_add_improvements extends Migration
{
    public function up()
    {
        // --- user: мессенджеры ---
        $this->addColumn('user', 'telegram_chat_id',
            $this->string(50)->null()->after('status'));
        $this->addColumn('user', 'vk_id',
            $this->string(50)->null()->after('telegram_chat_id'));

        // --- homework_answer: снэпшот ответа и комментарий учителя ---
        $this->addColumn('homework_answer', 'correct_answer_snapshot',
            $this->string(500)->null()->after('answer_text'));
        $this->addColumn('homework_answer', 'teacher_comment',
            $this->text()->null()->after('points_earned'));

        // --- notification: добавляем типы для мессенджеров ---
        // Расширяем ENUM типов уведомлений
        $this->execute("
            ALTER TABLE notification
            MODIFY COLUMN type ENUM(
                'homework_assigned',
                'homework_submitted',
                'homework_reviewed',
                'deadline_reminder',
                'exam_assigned',
                'exam_finished',
                'result_ready',
                'lesson_published'
            ) NOT NULL
        ");

        // --- notification: канал доставки ---
        $this->addColumn('notification', 'channel',
            "ENUM('site','email','telegram','vk') NOT NULL DEFAULT 'site' AFTER type"
        );

        // --- user: поиск по задачам (FULLTEXT) ---
        $this->execute('ALTER TABLE task ADD FULLTEXT INDEX ft_task_content (content)');
        $this->execute('ALTER TABLE task ADD FULLTEXT INDEX ft_task_title (title)');

        // --- activity_log: лог действий ---
        $this->createTable('activity_log', [
            'id'          => $this->primaryKey(),
            'user_id'     => $this->integer()->notNull(),
            'action'      => $this->string(100)->notNull(),
            'entity_type' => $this->string(50)->null(),
            'entity_id'   => $this->integer()->null(),
            'data'        => $this->text()->null(),
            'ip'          => $this->string(45)->null(),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_activity_log_user',   'activity_log', 'user_id');
        $this->createIndex('idx_activity_log_action', 'activity_log', 'action');
        $this->createIndex('idx_activity_log_entity', 'activity_log', ['entity_type', 'entity_id']);

        $this->addForeignKey('fk_activity_log_user',
            'activity_log', 'user_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        // --- course: курсовая модель (фундамент) ---
        $this->createTable('course', [
            'id'          => $this->primaryKey(),
            'title'       => $this->string(255)->notNull(),
            'slug'        => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'price'       => $this->integer()->notNull()->defaultValue(0),
            'status'      => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            'created_by'  => $this->integer()->notNull(),
            'created_at'  => $this->integer()->notNull(),
            'updated_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_course_slug', 'course', 'slug', true);

        $this->addForeignKey('fk_course_created_by',
            'course', 'created_by',
            'user', 'id',
            'RESTRICT', 'CASCADE'
        );

        $this->createTable('course_lesson', [
            'id'         => $this->primaryKey(),
            'course_id'  => $this->integer()->notNull(),
            'lesson_id'  => $this->integer()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('idx_course_lesson_unique',
            'course_lesson', ['course_id', 'lesson_id'], true);

        $this->addForeignKey('fk_course_lesson_course',
            'course_lesson', 'course_id',
            'course', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_course_lesson_lesson',
            'course_lesson', 'lesson_id',
            'lesson', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('course_enrollment', [
            'id'             => $this->primaryKey(),
            'course_id'      => $this->integer()->notNull(),
            'student_id'     => $this->integer()->notNull(),
            'enrolled_at'    => $this->integer()->notNull(),
            'expires_at'     => $this->integer()->null(),
            'payment_status' => "ENUM('free','paid','pending') NOT NULL DEFAULT 'free'",
        ]);

        $this->createIndex('idx_course_enrollment_unique',
            'course_enrollment', ['course_id', 'student_id'], true);

        $this->addForeignKey('fk_course_enrollment_course',
            'course_enrollment', 'course_id',
            'course', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_course_enrollment_student',
            'course_enrollment', 'student_id',
            'user', 'id', 'CASCADE', 'CASCADE');

        // --- student_progress: прогресс по курсу ---
        $this->createTable('student_progress', [
            'id'                 => $this->primaryKey(),
            'student_id'         => $this->integer()->notNull(),
            'course_id'          => $this->integer()->notNull(),
            'lessons_completed'  => $this->smallInteger()->notNull()->defaultValue(0),
            'lessons_total'      => $this->smallInteger()->notNull()->defaultValue(0),
            'percent'            => $this->smallInteger()->notNull()->defaultValue(0),
            'last_activity_at'   => $this->integer()->null(),
        ]);

        $this->createIndex('idx_student_progress_unique',
            'student_progress', ['student_id', 'course_id'], true);

        $this->addForeignKey('fk_student_progress_student',
            'student_progress', 'student_id',
            'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_student_progress_course',
            'student_progress', 'course_id',
            'course', 'id', 'CASCADE', 'CASCADE');

        // --- achievement: достижения ---
        $this->createTable('achievement', [
            'id'             => $this->primaryKey(),
            'title'          => $this->string(100)->notNull(),
            'description'    => $this->string(500)->notNull()->defaultValue(''),
            'icon'           => $this->string(100)->notNull()->defaultValue(''),
            'condition_type' => $this->string(50)->notNull(),
            'condition_value'=> $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createTable('student_achievement', [
            'id'             => $this->primaryKey(),
            'student_id'     => $this->integer()->notNull(),
            'achievement_id' => $this->integer()->notNull(),
            'earned_at'      => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_student_achievement_unique',
            'student_achievement', ['student_id', 'achievement_id'], true);

        $this->addForeignKey('fk_student_achievement_student',
            'student_achievement', 'student_id',
            'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_student_achievement_achievement',
            'student_achievement', 'achievement_id',
            'achievement', 'id', 'CASCADE', 'CASCADE');

        // --- user: флаг самостоятельной регистрации ---
        $this->addColumn('user', 'is_self_registered',
            $this->boolean()->notNull()->defaultValue(false)->after('status'));
    }

    public function down()
    {
        $this->dropTable('student_achievement');
        $this->dropTable('achievement');
        $this->dropTable('student_progress');
        $this->dropTable('course_enrollment');
        $this->dropTable('course_lesson');
        $this->dropTable('course');
        $this->dropTable('activity_log');

        $this->dropColumn('user', 'is_self_registered');
        $this->dropColumn('user', 'vk_id');
        $this->dropColumn('user', 'telegram_chat_id');
        $this->dropColumn('homework_answer', 'teacher_comment');
        $this->dropColumn('homework_answer', 'correct_answer_snapshot');
        $this->dropColumn('notification', 'channel');

        $this->execute("
            ALTER TABLE notification
            MODIFY COLUMN type ENUM(
                'homework_assigned',
                'homework_submitted',
                'deadline_reminder',
                'exam_assigned',
                'result_ready'
            ) NOT NULL
        ");
    }
}