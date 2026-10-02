<?php

namespace app\models;

class LessonTopic extends BaseModel
{
    public static function tableName(): string
    {
        return 'lesson_topic';
    }

    public function rules(): array
    {
        return [
            [['title', 'slug'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 500],
            [['slug'], 'unique'],
            [['sort_order'], 'integer'],
        ];
    }

    public function getLessons(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Lesson::class, ['topic_id' => 'id'])->orderBy('created_at');
    }
}