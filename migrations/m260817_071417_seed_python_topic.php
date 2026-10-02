<?php

use yii\db\Migration;

class m260817_071417_seed_python_topic extends Migration
{
    public function up()
    {
        $adminId = (new \yii\db\Query())->select('id')->from('user')->where(['role' => 'admin'])->scalar();
        if (!$adminId) return;

        $this->insert('lesson_topic', [
            'title'       => 'Python',
            'slug'        => 'python',
            'description' => 'Базовый Python для решения задач ЕГЭ — без углубления в профессиональную разработку.',
            'sort_order'  => 0,
            'created_at'  => time(),
        ]);
        $topicId = $this->db->getLastInsertID();

        $lessons = [
            'Введение и синтаксис Python',
            'Переменные и типы данных',
            'Ввод и вывод данных',
            'Условные операторы',
            'Циклы for и while',
            'Списки и работа с ними',
            'Строки и их методы',
            'Функции',
            'Словари',
            'Разбор типовых задач ЕГЭ на Python',
        ];

        $lessonIds = [];
        foreach ($lessons as $i => $title) {
            $this->insert('lesson', [
                'teacher_id'  => $adminId,
                'topic_id'    => $topicId,
                'title'       => $title,
                'lesson_type' => 'practice',
                'status'      => 'published',
                'created_at'  => time() + $i,
            ]);
            $lessonIds[] = $this->db->getLastInsertID();
        }

        // Готовая колода слайдов для первого урока
        $this->insert('slide_deck', [
            'lesson_id'  => $lessonIds[0],
            'teacher_id' => $adminId,
            'title'      => 'Введение и синтаксис Python',
            'status'     => 'ready',
            'created_at' => time(),
        ]);
        $deckId = $this->db->getLastInsertID();

        $slides = [
            ["# Python для ЕГЭ\n\nВ этом курсе мы разберём **только то**, что нужно для решения заданий ЕГЭ по информатике.\n\nPython часто используется в заданиях 6, 8, 15, 16, 23–27.", null],
            ["## Как запустить код\n\nДля тренировки:\n- [Python Tutor](https://pythontutor.com)\n- [replit.com](https://replit.com)\n- Установленный Python\n\nПрограмма — это набор команд, выполняемых построчно сверху вниз.", 'Покажи один из онлайн-редакторов на экране.'],
            ["## Первая программа\n\n```python\nprint(\"Привет, ЕГЭ!\")\n```\n\nФункция `print()` выводит значение на экран.", null],
            ["## Комментарии\n\n```python\n# Это комментарий, Python его игнорирует\nprint(\"Код выполняется\")  # так тоже можно\n```", null],
            ["## Отступы имеют значение\n\n```python\nif 5 > 3:\n    print(\"Верно\")\n```\n\nВ Python отступы — часть синтаксиса. Ошибка в отступе — частая причина падения программы на экзамене.", 'Особенность отличается от большинства других языков — акцентируй внимание.'],
            ["## Что дальше\n\nВ следующих темах разберём:\n- Переменные и типы данных\n- Условия и циклы\n- Списки и строки\n\nЭтого достаточно для большинства заданий ЕГЭ на Python.", null],
        ];

        foreach ($slides as $i => $pair) {
            $this->insert('slide', [
                'deck_id'    => $deckId,
                'sort_order' => $i,
                'content'    => $pair[0],
                'notes'      => $pair[1],
                'created_at' => time(),
            ]);
        }
    }

    public function down()
    {
        $this->execute("DELETE FROM slide WHERE deck_id IN (SELECT id FROM slide_deck WHERE lesson_id IN (SELECT id FROM lesson WHERE topic_id IN (SELECT id FROM lesson_topic WHERE slug='python')))");
        $this->execute("DELETE FROM slide_deck WHERE lesson_id IN (SELECT id FROM lesson WHERE topic_id IN (SELECT id FROM lesson_topic WHERE slug='python'))");
        $this->execute("DELETE FROM lesson WHERE topic_id IN (SELECT id FROM lesson_topic WHERE slug='python')");
        $this->execute("DELETE FROM lesson_topic WHERE slug='python'");
    }
}
