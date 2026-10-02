<?php

use yii\db\Migration;

class m260709_072500_add_lesson_content_type extends Migration
{
    public function up()
    {
        // Тип темы: practice — с задачами, info — просто текст без баллов
        $this->addColumn('lesson', 'lesson_type', "ENUM('practice','info') NOT NULL DEFAULT 'practice' AFTER title");
        $this->addColumn('lesson', 'info_content', $this->text()->null()->after('lesson_type'));

        // Отметка "прочитано" информационного модуля учеником
        $this->createTable('lesson_read_mark', [
            'id'         => $this->primaryKey(),
            'lesson_id'  => $this->integer()->notNull(),
            'student_id' => $this->integer()->notNull(),
            'read_at'    => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_lesson_read_unique', 'lesson_read_mark', ['lesson_id', 'student_id'], true);

        $this->addForeignKey('fk_lesson_read_lesson', 'lesson_read_mark', 'lesson_id', 'lesson', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_lesson_read_student', 'lesson_read_mark', 'student_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('lesson_read_mark');
        $this->dropColumn('lesson', 'info_content');
        $this->dropColumn('lesson', 'lesson_type');
    }
}
