<?php

namespace app\models;

class ExamAttemptAnswer extends BaseModel
{
    public static function tableName(): string
    {
        return 'exam_attempt_answer';
    }

    public function getAttempt(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ExamAttempt::class, ['id' => 'attempt_id']);
    }

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}