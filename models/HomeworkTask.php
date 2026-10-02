<?php

namespace app\models;

class HomeworkTask extends BaseModel
{
    public static function tableName(): string
    {
        return 'homework_task';
    }

    public function getHomework(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Homework::class, ['id' => 'homework_id']);
    }

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}