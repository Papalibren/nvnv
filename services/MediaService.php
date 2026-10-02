<?php

namespace app\services;

use Yii;
use yii\web\UploadedFile;
use app\models\MediaFile;

class MediaService
{
    public function upload(UploadedFile $file, int $userId): MediaFile
    {
        $path = Yii::$app->storage->save($file, 'media');

        $media              = new MediaFile();
        $media->filename    = $file->name;
        $media->path        = $path;
        $media->mime_type   = $file->type;
        $media->size        = $file->size;
        $media->uploaded_by = $userId;

        // Получаем размеры если это изображение
        if (str_starts_with($file->type, 'image/')) {
            $fullPath = Yii::getAlias('@app/storage') . '/' . $path;
            $info     = @getimagesize($fullPath);
            if ($info) {
                $media->width  = $info[0];
                $media->height = $info[1];
            }
        }

        $media->save();
        return $media;
    }
}