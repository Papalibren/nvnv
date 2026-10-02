<?php

namespace app\models;

use Yii;

class MediaFile extends BaseModel
{
    public static function tableName(): string
    {
        return 'media_file';
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

    public function getUploadedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getUrl(): string
    {
        return Yii::$app->storage->url($this->path);
    }

    public function getFormattedSize(): string
    {
        $size = $this->size;
        if ($size < 1024) return $size . ' Б';
        if ($size < 1024 * 1024) return round($size / 1024, 1) . ' КБ';
        return round($size / 1024 / 1024, 1) . ' МБ';
    }
}