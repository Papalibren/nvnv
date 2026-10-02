<?php

namespace app\models;

class CourseLesson extends BaseModel
{
    public static function tableName(): string
    {
        return 'course_lesson';
    }

    public function getLesson(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    public function getCourse(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getCheckpointExam(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Exam::class, ['id' => 'checkpoint_exam_id']);
    }

    public function isCheckpoint(): bool
    {
        return $this->checkpoint_exam_id !== null;
    }
}