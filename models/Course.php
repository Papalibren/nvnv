<?php

namespace app\models;

class Course extends BaseModel
{
    public static function tableName(): string
    {
        return 'course';
    }

    public function getCourseLessons(): \yii\db\ActiveQuery
    {
        return $this->hasMany(CourseLesson::class, ['course_id' => 'id'])->orderBy('sort_order');
    }
}