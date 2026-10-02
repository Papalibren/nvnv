<?php

use yii\db\Migration;

class m260705_080039_add_lead_notification_type extends Migration
{
    public function up()
    {
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
                'lesson_published',
                'lead_received'
            ) NOT NULL
        ");
    }

    public function down()
    {
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
    }
}
