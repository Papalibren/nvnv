<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\models\Landing;

class LandingController extends BaseAdminController
{
    public function actionIndex()
    {
        $this->view->title = 'Лендинги';

        $landings = Landing::find()->orderBy('created_at DESC')->all();

        return $this->render('index', ['landings' => $landings]);
    }

    public function actionCreate()
    {
        $this->view->title = 'Новый лендинг';

        $error = null;

        if (Yii::$app->request->isPost) {
            $error = $this->saveLanding(null, Yii::$app->request->post());

            if ($error === null) {
                $landing = Landing::find()->orderBy('id DESC')->one();
                Yii::$app->session->setFlash('success', 'Лендинг создан.');
                return $this->redirect(['/admin/landing/update', 'id' => $landing->id]);
            }
        }

        return $this->render('form', ['landing' => null, 'error' => $error, 'isNew' => true]);
    }

    public function actionUpdate(int $id)
    {
        $landing = $this->findLanding($id);
        $this->view->title = $landing->title;

        $error = null;

        if (Yii::$app->request->isPost) {
            $error = $this->saveLanding($landing, Yii::$app->request->post());

            if ($error === null) {
                Yii::$app->session->setFlash('success', 'Лендинг сохранён.');
                return $this->redirect(['/admin/landing/update', 'id' => $landing->id]);
            }
        }

        return $this->render('form', ['landing' => $landing, 'error' => $error, 'isNew' => false]);
    }

    public function actionDelete(int $id)
    {
        $landing = $this->findLanding($id);
        $landing->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

    public function actionToggleStatus(int $id)
    {
        $landing = $this->findLanding($id);
        $landing->status = $landing->status === Landing::STATUS_PUBLISHED
            ? Landing::STATUS_DRAFT
            : Landing::STATUS_PUBLISHED;
        $landing->save(false);

        return $this->renderPartial('_status_badge', ['landing' => $landing]);
    }

    private function saveLanding(?Landing $landing, array $data): ?string
    {
        $isNew = $landing === null;
        if ($isNew) {
            $landing = new Landing();
        }

        $landing->title            = trim($data['title'] ?? '');
        $landing->meta_title       = trim($data['meta_title'] ?? '') ?: $landing->title;
        $landing->meta_description = trim($data['meta_description'] ?? '');
        $landing->form_title       = trim($data['form_title'] ?? '') ?: 'Оставить заявку';
        $landing->content          = $data['content'] ?? '';
        $landing->status           = $data['status'] ?? Landing::STATUS_DRAFT;
        $landing->is_indexed       = isset($data['is_indexed']) ? 1 : 0;

        if ($landing->title === '') return 'Введите название.';

        if ($isNew) {
            $customSlug = trim($data['slug'] ?? '');
            $baseSlug   = $customSlug !== '' ? \yii\helpers\Inflector::slug($customSlug)
                                              : \yii\helpers\Inflector::slug($landing->title);
            $slug = $baseSlug;
            $i    = 1;
            while (Landing::find()->where(['slug' => $slug])->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $landing->slug = $slug;
        }

        // Загрузка og:image
        $ogImage = UploadedFile::getInstanceByName('og_image_file');
        if ($ogImage) {
            $path = Yii::$app->storage->save($ogImage, 'landing-og');
            $landing->og_image = $path;
        }

        if (!$landing->save()) {
            return implode(', ', $landing->getFirstErrors());
        }

        return null;
    }

    private function findLanding(int $id): Landing
    {
        $landing = Landing::findOne($id);
        if (!$landing) throw new NotFoundHttpException('Лендинг не найден.');
        return $landing;
    }
    public function actionPreview()
    {
        $content   = Yii::$app->request->post('content', '');
        $formTitle = Yii::$app->request->post('form_title', 'Оставить заявку');

        return $this->renderPartial('@app/views/landing/_preview_wrapper', [
            'content'   => $content,
            'formTitle' => $formTitle,
        ]);
    }
}