<?php
namespace app\models;
use Yii;

class TaskFile extends BaseModel
{
    public static function tableName(): string
    {
        return 'task_file';
    }

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public function getUrl(): string
    {
        return Yii::$app->storage->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}