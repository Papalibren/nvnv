<?php

use yii\db\Migration;

class m260702_124809_make_task_number_nullable extends Migration
{
    public function up()
    {
        $this->alterColumn('task', 'task_number',
            $this->smallInteger()->null()->defaultValue(null));
    }

    public function down()
    {
        $this->alterColumn('task', 'task_number',
            $this->smallInteger()->notNull());
    }
}
