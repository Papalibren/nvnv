<?php

use yii\db\Migration;

class m260706_032254_seed_default_self_study_course extends Migration
{
    public function up()
    {
        $adminId = (new \yii\db\Query())
            ->select('id')->from('user')
            ->where(['role' => 'admin'])
            ->scalar();

        if (!$adminId) return;

        $this->insert('course', [
            'title'       => 'Самостоятельная подготовка к ЕГЭ',
            'slug'        => 'samostoyatelnaya-podgotovka-ege',
            'description' => 'Свободный темп: теория, практика и пробные экзамены без привязки к датам.',
            'price'       => 0,
            'status'      => 'published',
            'created_by'  => $adminId,
            'created_at'  => time(),
            'updated_at'  => time(),
        ]);

        $courseId = $this->db->getLastInsertID();

        $topics = [
            'Знакомство с форматом ЕГЭ',
            'Системы счисления',
            'Логические выражения',
            'Алгоритмы и программы',
            'Работа с таблицами и базами данных',
        ];

        foreach ($topics as $i => $topic) {
            $this->insert('lesson', [
                'teacher_id'   => $adminId,
                'title'        => $topic,
                'status'       => 'published',
                'created_at'   => time(),
            ]);

            $lessonId = $this->db->getLastInsertID();

            $this->insert('course_lesson', [
                'course_id'  => $courseId,
                'lesson_id'  => $lessonId,
                'sort_order' => $i,
            ]);
        }
    }

    public function down() {}
}
