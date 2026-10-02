<?php

namespace app\models;

class CourseScheduleItem extends BaseModel
{
    public static function tableName(): string
    {
        return 'course_schedule_item';
    }

    public function getCourseLesson(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CourseLesson::class, ['id' => 'course_lesson_id']);
    }

    public function getHomework(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Homework::class, ['id' => 'homework_id']);
    }

    public function getEnrollment(): \yii\db\ActiveQuery
    {
        return $this->hasOne(CourseEnrollment::class, ['id' => 'enrollment_id']);
    }

    public function isUnlocked(): bool
    {
        return $this->unlock_at <= time();
    }
}