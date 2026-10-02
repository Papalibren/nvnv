<?php

namespace app\models;

class SlideDeck extends BaseModel
{
    public static function tableName(): string
    {
        return 'slide_deck';
    }

    public function getLesson(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    public function getTeacher(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    public function getSlides(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Slide::class, ['deck_id' => 'id'])->orderBy('sort_order');
    }
}