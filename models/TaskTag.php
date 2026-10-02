<?php

namespace app\models;

class TaskTag extends BaseModel
{
    public static function tableName(): string
    {
        return 'task_tag';
    }

    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['name', 'slug'], 'string', 'max' => 100],
            [['slug'], 'unique'],
        ];
    }

    public function getTasks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
                    ->viaTable('task_tag_pivot', ['tag_id' => 'id']);
    }
    public function getTaskCount(): int
    {
        return (int) $this->getTasks()->count();
    }
}