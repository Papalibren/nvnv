<?php

namespace app\models;

class CourseEnrollment extends BaseModel
{
    public static function tableName(): string
    {
        return 'course_enrollment';
    }
    public function getCourse(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    public function getScheduleItems(): \yii\db\ActiveQuery
    {
        return $this->hasMany(CourseScheduleItem::class, ['enrollment_id' => 'id'])
                    ->orderBy('unlock_at');
    }
}