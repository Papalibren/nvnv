<?php

namespace app\controllers\admin;

use Yii;
use yii\web\UploadedFile;
use app\models\MediaFile;
use app\services\MediaService;

class MediaController extends BaseAdminController
{
    /**
     * Возвращает партиал с галереей — используется в модалке выбора
     */
    public function actionPicker()
    {
        $files = MediaFile::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(60)
            ->all();

        return $this->renderPartial('@app/views/admin/media/_picker', [
            'files' => $files,
        ]);
    }

    /**
     * Загрузка нового файла прямо из модалки
     */
    public function actionUpload()
    {
        $file = UploadedFile::getInstanceByName('file');

        if (!$file) {
            Yii::$app->response->statusCode = 400;
            return 'Файл не выбран';
        }

        $service = new MediaService();
        $media   = $service->upload($file, Yii::$app->user->id);

        // Возвращаем обновлённую галерею
        $files = MediaFile::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(60)
            ->all();

        return $this->renderPartial('@app/views/admin/media/_picker', [
            'files' => $files,
        ]);
    }
}