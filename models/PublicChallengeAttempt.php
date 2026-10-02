<?php

namespace app\models;

class PublicChallengeAttempt extends BaseModel
{
    public static function tableName(): string
    {
        return 'public_challenge_attempt';
    }

    public function getChallenge(): \yii\db\ActiveQuery
    {
        return $this->hasOne(PublicChallenge::class, ['id' => 'challenge_id']);
    }
}