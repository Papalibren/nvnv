<?php

namespace app\models;

class Exam extends BaseModel
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_FINISHED  = 'finished';

    public static function tableName(): string
    {
        return 'exam';
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
            [['teacher_id', 'group_id', 'duration_minutes'], 'integer'],
            [['is_full_scored', 'is_proctored', 'is_public'], 'boolean'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_FINISHED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title'            => 'Название',
            'duration_minutes' => 'Продолжительность (мин)',
            'is_full_scored'   => 'Полный балл (0-100)',
            'is_proctored'     => 'Защищённый режим (для рейтинга)',
            'status'           => 'Статус',
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

    public function getExamTasks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ExamTask::class, ['exam_id' => 'id'])->orderBy('sort_order');
    }

    public function getAttempts(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ExamAttempt::class, ['exam_id' => 'id']);
    }

    public function hasTimer(): bool
    {
        return $this->duration_minutes !== null && $this->duration_minutes > 0;
    }

    public function getStudentAttempt(int $studentId): ?ExamAttempt
    {
        return ExamAttempt::findOne(['exam_id' => $this->id, 'student_id' => $studentId]);
    }
}