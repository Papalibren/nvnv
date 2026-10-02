<?php

namespace app\models;

class HomeworkAnswer extends BaseModel
{
    public static function tableName(): string
    {
        return 'homework_answer';
    }

    public function getHomeworkStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(HomeworkStudent::class, ['id' => 'homework_student_id']);
    }

    public function getHomeworkTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(HomeworkTask::class, ['id' => 'homework_task_id']);
    }

    public function isFirstAttempt(): bool
    {
        return $this->attempt_number === 1;
    }
}