<?php

namespace app\models;

class Lesson extends BaseModel
{
    const TYPE_PRACTICE = 'practice';
    const TYPE_INFO      = 'info';

    public static function tableName(): string
    {
        return 'lesson';
    }

    public function rules(): array
    {
        return [
            [['title', 'teacher_id'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description', 'info_content'], 'string'],
            [['teacher_id', 'group_id', 'topic_id', 'scheduled_at'], 'integer'],
            [['lesson_type'], 'in', 'range' => [self::TYPE_PRACTICE, self::TYPE_INFO]],
            [['status'], 'in', 'range' => ['draft', 'published']],
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

    public function getTopic(): \yii\db\ActiveQuery
    {
        return $this->hasOne(LessonTopic::class, ['id' => 'topic_id']);
    }

    public function getSlideDecks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(SlideDeck::class, ['lesson_id' => 'id'])->orderBy('created_at');
    }

    public function getBookPages(): \yii\db\ActiveQuery
    {
        return BookPage::find()
            ->innerJoin('lesson_theory_link ltl', 'ltl.content_id = book_page.id')
            ->where(['ltl.lesson_id' => $this->id, 'ltl.content_type' => 'book_page']);
    }

    public function isInfo(): bool
    {
        return $this->lesson_type === self::TYPE_INFO;
    }

    public function isReadBy(int $studentId): bool
    {
        return LessonReadMark::find()
            ->where(['lesson_id' => $this->id, 'student_id' => $studentId])
            ->exists();
    }
}