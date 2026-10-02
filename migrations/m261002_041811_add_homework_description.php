<?php

use yii\db\Migration;

class m261002_041811_add_homework_description extends Migration
{
    public function up()
    {
        $this->addColumn('homework', 'description', $this->text()->null()->after('title'));
        $this->addColumn('homework', 'oral_questions', $this->text()->null()->after('description'));
    }

    public function down()
    {
        $this->dropColumn('homework', 'oral_questions');
        $this->dropColumn('homework', 'description');
    }
}
