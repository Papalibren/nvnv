<?php

namespace app\models;

class BookPage extends BaseModel
{
    public static function tableName(): string
    {
        return 'book_page';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['title', 'slug', 'chapter_id'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['content'], 'string'],
            [['sort_order', 'chapter_id', 'published_at'], 'integer'],
            [['seo_title'], 'string', 'max' => 255],
            [['seo_description'], 'string', 'max' => 300],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title'        => 'Заголовок',
            'slug'         => 'URL',
            'content'      => 'Содержимое',
            'sort_order'   => 'Порядок',
            'published_at' => 'Дата публикации',
            'seo_title'          => 'Title SEO',
            'seo_description'    => 'Description SEO'
        ];
    }

    public function getChapter(): \yii\db\ActiveQuery
    {
        return $this->hasOne(BookChapter::class, ['id' => 'chapter_id']);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at <= time();
    }

    public function getTasks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
            ->viaTable('task_theory_link', ['book_page_id' => 'id']);
    }
    public function getUrl(): string
    {
        $chapter = $this->chapter;
        $section = $chapter->section ?? null;

        if (!$section) {
            return '/book';
        }

        return '/book/' . $section->slug . '/' . $chapter->slug . '/' . $this->slug;
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
