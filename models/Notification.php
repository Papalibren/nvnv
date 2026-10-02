<?php

namespace app\models;

class Notification extends BaseModel
{
    const TYPE_HOMEWORK_ASSIGNED  = 'homework_assigned';
    const TYPE_HOMEWORK_SUBMITTED = 'homework_submitted';
    const TYPE_HOMEWORK_REVIEWED  = 'homework_reviewed';
    const TYPE_DEADLINE_REMINDER  = 'deadline_reminder';
    const TYPE_EXAM_ASSIGNED      = 'exam_assigned';
    const TYPE_EXAM_FINISHED      = 'exam_finished';
    const TYPE_RESULT_READY       = 'result_ready';
    const TYPE_LESSON_PUBLISHED   = 'lesson_published';
    const TYPE_LEAD_RECEIVED = 'lead_received';

    const CHANNEL_SITE     = 'site';
    const CHANNEL_EMAIL    = 'email';
    const CHANNEL_TELEGRAM = 'telegram';
    const CHANNEL_VK       = 'vk';

    public static function tableName(): string
    {
        return 'notification';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function countUnread(int $userId): int
    {
        return (int) static::find()
            ->where(['user_id' => $userId, 'is_read' => false])
            ->count();
    }

    public static function markAllRead(int $userId): void
    {
        static::updateAll(['is_read' => true], ['user_id' => $userId, 'is_read' => false]);
    }
}