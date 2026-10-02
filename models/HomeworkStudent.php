<?php

namespace app\models;

class HomeworkStudent extends BaseModel
{
    const STATUS_ASSIGNED    = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED   = 'submitted';
    const STATUS_REVIEWED    = 'reviewed';

    public static function tableName(): string
    {
        return 'homework_student';
    }

    public function getHomework(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Homework::class, ['id' => 'homework_id']);
    }

    public function getStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'student_id']);
    }

    public function getAnswers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(HomeworkAnswer::class, ['homework_student_id' => 'id']);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED
            || $this->status === self::STATUS_REVIEWED;
    }

    public static function getLabels(): array
    {
        return [
            self::STATUS_ASSIGNED    => 'Назначено',
            self::STATUS_IN_PROGRESS => 'Выполняется',
            self::STATUS_SUBMITTED   => 'Сдано',
            self::STATUS_REVIEWED    => 'Проверено',
        ];
    }

    public function wasSubmittedLate(): bool
    {
        if (!$this->submitted_at || !$this->homework->deadline_at) {
            return false;
        }
        return $this->submitted_at > $this->homework->deadline_at;
    }
}