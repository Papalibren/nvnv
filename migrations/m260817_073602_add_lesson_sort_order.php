<?php

use yii\db\Migration;

class m260817_073602_add_lesson_sort_order extends Migration
{
    public function up()
    {
        $this->addColumn('lesson', 'sort_order', $this->integer()->notNull()->defaultValue(0)->after('topic_id'));

        // Проставляем начальный порядок по дате создания
        $this->execute("
            SET @rn := 0;
            UPDATE lesson SET sort_order = (@rn := @rn + 1) ORDER BY created_at
        ");
    }

    public function down()
    {
        $this->dropColumn('lesson', 'sort_order');
    }
}
