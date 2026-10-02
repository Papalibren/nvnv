<?php

use yii\db\Migration;

class m261001_074537_add_task_answer_type extends Migration
{
    public function up()
    {
        $this->addColumn('task', 'answer_type', "ENUM('exact','manual') NOT NULL DEFAULT 'exact' AFTER answer");

        $this->addColumn('homework_answer', 'needs_manual_review', $this->boolean()->notNull()->defaultValue(0));
        $this->addColumn('exam_attempt_answer', 'needs_manual_review', $this->boolean()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('exam_attempt_answer', 'needs_manual_review');
        $this->dropColumn('homework_answer', 'needs_manual_review');
        $this->dropColumn('task', 'answer_type');
    }
}
