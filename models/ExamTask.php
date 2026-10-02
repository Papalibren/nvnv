<?php

namespace app\models;

class ExamTask extends BaseModel
{
    public static function tableName(): string
    {
        return 'exam_task';
    }

    public function getExam(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Exam::class, ['id' => 'exam_id']);
    }

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}