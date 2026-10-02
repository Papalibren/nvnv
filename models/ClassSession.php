<?php

namespace app\models;

class ClassSession extends BaseModel
{
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return 'class_session';
    }

    public function getTeacher(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    public function getStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'student_id']);
    }

    public function getGroup(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    public function getLesson(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    public function getHomework(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Homework::class, ['id' => 'homework_id']);
    }

    public function getExam(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Exam::class, ['id' => 'exam_id']);
    }

    public function isPast(): bool
    {
        return $this->scheduled_at < time();
    }

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Запланировано',
            self::STATUS_COMPLETED => 'Проведено',
            self::STATUS_CANCELLED => 'Отменено',
        ];
    }
}