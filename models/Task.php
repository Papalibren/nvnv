<?php

namespace app\models;

class Task extends BaseModel
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const ANSWER_TYPE_EXACT  = 'exact';
    const ANSWER_TYPE_MANUAL = 'manual';

    public static function tableName(): string
    {
        return 'task';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['content', 'answer', 'created_by'], 'required'],
            [['task_number'], 'integer', 'min' => 1, 'max' => 27],
            [['task_number'], 'default', 'value' => null],
            [['difficulty'], 'integer', 'min' => 1, 'max' => 10],
            [['content', 'solution_content'], 'string'],
            [['answer'], 'string', 'max' => 500],
            [['title'], 'string', 'max' => 255],
            [['solution_is_public', 'has_file'], 'boolean'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['seo_title'], 'string', 'max' => 255],
            [['seo_description'], 'string', 'max' => 300],
            [['answer_type'], 'in', 'range' => [self::ANSWER_TYPE_EXACT, self::ANSWER_TYPE_MANUAL]],
            [
                ['answer'],
                'required',
                'when' => fn($model) => $model->answer_type === self::ANSWER_TYPE_EXACT,
                'message' => 'Укажите эталонный ответ (или выберите тип "Развёрнутый — проверка вручную").'
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'task_number'        => 'Номер задания',
            'title'              => 'Заголовок',
            'content'            => 'Условие',
            'answer'             => 'Ответ',
            'solution_content'   => 'Разбор',
            'solution_is_public' => 'Разбор публичен',
            'difficulty'         => 'Сложность',
            'has_file'           => 'Есть файл',
            'status'             => 'Статус',
            'seo_title'          => 'Title SEO',
            'seo_description'    => 'Description SEO'
        ];
    }

    // ==================
    // Связи
    // ==================

    public function getTags(): \yii\db\ActiveQuery
    {
        return $this->hasMany(TaskTag::class, ['id' => 'tag_id'])
            ->viaTable('task_tag_pivot', ['task_id' => 'id']);
    }

    public function getFiles(): \yii\db\ActiveQuery
    {
        return $this->hasMany(TaskFile::class, ['task_id' => 'id']);
    }

    public function getBookPages(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BookPage::class, ['id' => 'book_page_id'])
            ->viaTable('task_theory_link', ['task_id' => 'id']);
    }

    public function getCreatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    // ==================
    // Хелперы
    // ==================

    public function getDifficultyColor(): string
    {
        return match (true) {
            $this->difficulty <= 3  => 'acid-lime',
            $this->difficulty <= 7  => 'acid-cyan',
            default                 => 'acid-pink',
        };
    }

    /**
     * Возвращает true/false для точного ответа, null для задач с ручной проверкой
     * (означает "нельзя автоматически определить правильность").
     */
    public function checkAnswer(string $userAnswer): ?bool
    {
        if ($this->answer_type === self::ANSWER_TYPE_MANUAL) {
            return null;
        }
        return mb_strtolower(trim($userAnswer)) === mb_strtolower(trim($this->answer));
    }

    public function isManualAnswer(): bool
    {
        return $this->answer_type === self::ANSWER_TYPE_MANUAL;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public static function getLabels(): array
    {
        return [
            self::STATUS_DRAFT     => 'Черновик',
            self::STATUS_PUBLISHED => 'Опубликована',
        ];
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo_title ?: null;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description ?: null;
    }
}
