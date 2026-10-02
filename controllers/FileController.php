<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class FileController extends Controller
{
    public function actionGet(string $path)
    {
        $basePath = Yii::getAlias('@app/storage');
        $fullPath = $basePath . '/' . ltrim($path, '/');

        // Защита от path traversal
        $realBase = realpath($basePath);
        $realPath = realpath($fullPath);

        if ($realPath === false || strpos($realPath, $realBase) !== 0) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        if (!file_exists($realPath)) {
            throw new NotFoundHttpException('Файл не найден.');
        }

        // Файлы submissions — только авторизованным
        if (strpos($path, 'submissions/') === 0 && Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Требуется авторизация.');
        }

        return Yii::$app->response->sendFile($realPath, basename($realPath), [
            'inline' => true,
        ]);
    }
}