<?php

namespace app\models;

class PublicChallenge extends BaseModel
{
    public static function tableName(): string
    {
        return 'public_challenge';
    }

    public function getTask(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public function isOpen(): bool
    {
        $now = time();
        return $now >= $this->opens_at && $now <= $this->closes_at;
    }

    public function isUpcoming(): bool
    {
        return time() < $this->opens_at;
    }

    public function isClosed(): bool
    {
        return time() > $this->closes_at;
    }

    public function getAttemptFor(int $studentId): ?PublicChallengeAttempt
    {
        return PublicChallengeAttempt::findOne(['challenge_id' => $this->id, 'student_id' => $studentId]);
    }
    public function getAttempts(): \yii\db\ActiveQuery
    {
        return $this->hasMany(PublicChallengeAttempt::class, ['challenge_id' => 'id']);
    }
}