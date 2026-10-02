<?php

use yii\db\Migration;

class m260707_141324_create_course_schedule extends Migration
{
    public function up()
    {
        // Ученик сам выбирает срок при зачислении
        $this->addColumn('course_enrollment', 'duration_months', $this->smallInteger()->null()->after('enrolled_at'));
        $this->addColumn('course_enrollment', 'target_end_at', $this->integer()->null()->after('duration_months'));
        $this->addColumn('course_enrollment', 'duration_changed', $this->boolean()->notNull()->defaultValue(0)->after('target_end_at'));

        // Чекпоинт — админ вручную привязывает публичный экзамен к уроку курса
        $this->addColumn('course_lesson', 'checkpoint_exam_id', $this->integer()->null()->after('sort_order'));

        $this->addForeignKey(
            'fk_course_lesson_checkpoint_exam',
            'course_lesson', 'checkpoint_exam_id',
            'exam', 'id',
            'SET NULL', 'CASCADE'
        );

        // Расписание разблокировки — по одной записи на пару (зачисление, урок курса)
        $this->createTable('course_schedule_item', [
            'id'                => $this->primaryKey(),
            'enrollment_id'     => $this->integer()->notNull(),
            'course_lesson_id'  => $this->integer()->notNull(),
            'unlock_at'         => $this->integer()->notNull(),
            'homework_id'       => $this->integer()->null(), // персональное ДЗ созданное при разблокировке
            'completed_at'      => $this->integer()->null(),
        ]);

        $this->createIndex('idx_schedule_enrollment', 'course_schedule_item', 'enrollment_id');
        $this->createIndex('idx_schedule_unlock', 'course_schedule_item', 'unlock_at');

        $this->addForeignKey(
            'fk_schedule_enrollment',
            'course_schedule_item', 'enrollment_id',
            'course_enrollment', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_schedule_lesson',
            'course_schedule_item', 'course_lesson_id',
            'course_lesson', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_schedule_homework',
            'course_schedule_item', 'homework_id',
            'homework', 'id',
            'SET NULL', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('course_schedule_item');
        $this->dropColumn('course_lesson', 'checkpoint_exam_id');
        $this->dropColumn('course_enrollment', 'duration_changed');
        $this->dropColumn('course_enrollment', 'target_end_at');
        $this->dropColumn('course_enrollment', 'duration_months');
    }
}
