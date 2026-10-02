<?php

use yii\db\Migration;

class m260706_030516_remove_theory_demo_add_rating_fields extends Migration
{
    public function up()
    {
        // Переключаем связь задачи с "теорией" на страницы учебника
        $this->dropForeignKey('fk_task_theory_link_theory', 'task_theory_link');
        $this->renameColumn('task_theory_link', 'theory_topic_id', 'book_page_id');

        $this->addForeignKey(
            'fk_task_theory_link_book_page',
            'task_theory_link', 'book_page_id',
            'book_page', 'id',
            'CASCADE', 'CASCADE'
        );

        // Убираем демо и теорию как отдельные разделы
        $this->dropTable('demo_solution');
        $this->dropTable('demo_variant');
        $this->dropTable('theory_topic');

        // Публичный псевдоним для рейтинга (логин может быть приватным)
        $this->addColumn('user', 'display_name', $this->string(50)->null()->after('name'));

        // Экзамен помечается как "рейтинговый" — защищённый режим
        $this->addColumn('exam', 'is_proctored', $this->boolean()->notNull()->defaultValue(0)->after('is_full_scored'));

        // Метрики античита попытки экзамена
        $this->addColumn('exam_attempt', 'focus_lost_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('exam_attempt', 'fullscreen_exits', $this->integer()->notNull()->defaultValue(0));

        // Служебный лендинг — заявка на репетитора (кнопка в сайдбаре ведёт сюда)
        $this->insert('landing', [
            'title'            => 'Заявка на репетитора',
            'slug'             => 'zapros-repetitora',
            'meta_title'       => 'Записаться к репетитору',
            'meta_description' => 'Персональные занятия с репетитором для подготовки к ЕГЭ по информатике',
            'is_indexed'       => 0,
            'form_title'       => 'Оставить заявку репетитору',
            'content'          => '<div style="max-width:600px;margin:60px auto;padding:0 20px;text-align:center;font-family:Inter,sans-serif;"><h1 style="font-size:28px;font-weight:800;margin-bottom:12px;">Персональные занятия с репетитором</h1><p style="color:#64748B;font-size:16px;line-height:1.6;">Оставьте заявку — подберём удобное время и разберём именно ваши слабые темы.</p></div>',
            'status'           => 'published',
            'created_at'       => time(),
            'updated_at'       => time(),
        ]);
    }

    public function down() {}
}
