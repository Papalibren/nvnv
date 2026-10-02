<?php

namespace app\components\storage;

use yii\web\UploadedFile;

interface StorageInterface
{
    public function save(UploadedFile $file, string $category): string;
    public function url(string $path): string;
    public function delete(string $path): bool;
}