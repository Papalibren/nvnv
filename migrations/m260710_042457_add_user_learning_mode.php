<?php

use yii\db\Migration;

class m260710_042457_add_user_learning_mode extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'learning_mode',
            "ENUM('self_study','tutored') NULL DEFAULT NULL AFTER role");

        // Backfill — у кого уже есть привязка к учителю, помечаем tutored
        $this->execute("
            UPDATE user u
            SET learning_mode = 'tutored'
            WHERE u.role = 'student'
              AND EXISTS (SELECT 1 FROM teacher_student ts WHERE ts.student_id = u.id)
        ");

        // Остальным ученикам — self_study
        $this->execute("
            UPDATE user u
            SET learning_mode = 'self_study'
            WHERE u.role = 'student' AND learning_mode IS NULL
        ");
    }

    public function down()
    {
        $this->dropColumn('user', 'learning_mode');
    }
}
