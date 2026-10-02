<?php

namespace app\models;

class Landing extends BaseModel
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';

    public static function tableName(): string
    {
        return 'landing';
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
            [['title', 'slug'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['meta_title'], 'string', 'max' => 255],
            [['meta_description'], 'string', 'max' => 500],
            [['og_image', 'form_title'], 'string', 'max' => 500],
            [['slug'], 'unique'],
            [['content'], 'string'],
            [['is_indexed'], 'boolean'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title'            => 'Название',
            'slug'             => 'URL',
            'meta_title'       => 'SEO заголовок',
            'meta_description' => 'SEO описание',
            'content'          => 'Контент',
            'status'           => 'Статус',
        ];
    }

    public function getLeads(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Lead::class, ['landing_id' => 'id'])
                    ->orderBy(['created_at' => SORT_DESC]);
    }
}