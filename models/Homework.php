<?php

namespace app\models;

class Homework extends BaseModel
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';

    public static function tableName(): string
    {
        return 'homework';
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

    public function rules(): array
    {
        return [
            [['teacher_id', 'title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['teacher_id', 'group_id', 'lesson_id', 'deadline_at'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title'       => 'Название',
            'deadline_at' => 'Дедлайн',
            'status'      => 'Статус',
        ];
    }

    public function getTeacher(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    public function getGroup(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    public function getHomeworkTasks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(HomeworkTask::class, ['homework_id' => 'id'])
                    ->orderBy('sort_order');
    }

    public function getHomeworkStudents(): \yii\db\ActiveQuery
    {
        return $this->hasMany(HomeworkStudent::class, ['homework_id' => 'id']);
    }

    public function isOverdue(): bool
    {
        return $this->deadline_at !== null && $this->deadline_at < time();
    }

    public function getDeadlineCoefficient(): float
    {
        return $this->isOverdue() ? 0.5 : 1.0;
    }

    public static function getLabels(): array
    {
        return [
            self::STATUS_DRAFT     => 'Черновик',
            self::STATUS_PUBLISHED => 'Опубликовано',
        ];
    }

    public function getPendingReviewCount(): int
    {
        return (int) HomeworkStudent::find()
            ->where(['homework_id' => $this->id, 'status' => HomeworkStudent::STATUS_SUBMITTED])
            ->count();
    }
}