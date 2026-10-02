<?php

namespace app\components\storage;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class LocalStorage extends Component implements StorageInterface
{
    public string $basePath = '@app/storage';
    public string $baseUrl  = '/files';

    public function save(UploadedFile $file, string $category): string
    {
        $dir  = Yii::getAlias($this->basePath) . '/' . $category . '/' . date('Y/m');
        $name = Yii::$app->security->generateRandomString(12) . '.' . $file->extension;

        FileHelper::createDirectory($dir);
        $file->saveAs($dir . '/' . $name);

        return $category . '/' . date('Y/m') . '/' . $name;
    }

    public function url(string $path): string
    {
        return $this->baseUrl . '/' . $path;
    }

    public function delete(string $path): bool
    {
        $full = Yii::getAlias($this->basePath) . '/' . $path;
        return file_exists($full) && @unlink($full);
    }
}