<?php

namespace app\models;

class InviteToken extends BaseModel
{
    public static function tableName(): string
    {
        return 'invite_token';
    }

    public function getStudent(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'student_id']);
    }

    public function getCreatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function isExpired(): bool
    {
        return $this->expires_at < time();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }
}