<?php

namespace app\models;

class ExamAttempt extends BaseModel
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED   = 'submitted';
    const STATUS_EXPIRED     = 'expired';

    public static function tableName(): string
    {
        return 'exam_attempt';
    }

    public function getExam(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Exam::class, ['id' => 'exam_id']);
    }

    public function getStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'student_id']);
    }

    public function getAnswers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ExamAttemptAnswer::class, ['attempt_id' => 'id']);
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_EXPIRED]);
    }

    public function getRemainingSeconds(): int
    {
        if (!$this->exam->hasTimer()) {
            return PHP_INT_MAX;
        }

        $elapsed = time() - $this->started_at;
        $total   = $this->exam->duration_minutes * 60;

        return max(0, $total - $elapsed);
    }

    public function isTimeExpired(): bool
    {
        return $this->exam->hasTimer() && $this->getRemainingSeconds() === 0;
    }

    public static function getLabels(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'В процессе',
            self::STATUS_SUBMITTED   => 'Сдан',
            self::STATUS_EXPIRED     => 'Истёк по времени',
        ];
    }
}