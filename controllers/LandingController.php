<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\Landing;
use app\services\LandingService;

class LandingController extends Controller
{
    public $layout = '@app/views/layouts/landing';

    public function actionView(string $slug)
    {
        $landing = Landing::findOne(['slug' => $slug, 'status' => Landing::STATUS_PUBLISHED]);

        if (!$landing) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        // SEO мета-теги
        $this->view->title = $landing->meta_title ?: $landing->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $landing->meta_description]);

        if (!$landing->is_indexed) {
            $this->view->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow']);
        }

        // Open Graph
        $this->view->registerMetaTag(['property' => 'og:title', 'content' => $landing->meta_title ?: $landing->title]);
        $this->view->registerMetaTag(['property' => 'og:description', 'content' => $landing->meta_description]);
        $this->view->registerMetaTag(['property' => 'og:type', 'content' => 'website']);
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);

        if ($landing->og_image) {
            $this->view->registerMetaTag([
                'property' => 'og:image',
                'content'  => Yii::$app->params['siteUrl'] . Yii::$app->storage->url($landing->og_image),
            ]);
        }

        // Canonical
        $this->view->registerLinkTag(['rel' => 'canonical', 'href' => Yii::$app->request->absoluteUrl]);

        return $this->render('view', ['landing' => $landing]);
    }

    /**
     * Приём заявки с формы (HTMX)
     */
    public function actionCreateLead(int $landingId)
    {
        $landing = Landing::findOne(['id' => $landingId, 'status' => Landing::STATUS_PUBLISHED]);

        if (!$landing) {
            Yii::$app->response->statusCode = 404;
            return $this->renderPartial('_lead_error', ['message' => 'Лендинг не найден']);
        }

        $data = Yii::$app->request->post();

        if (empty($data['name']) || empty($data['phone'])) {
            return $this->renderPartial('_lead_error', ['message' => 'Заполните имя и телефон']);
        }

        try {
            $service = new LandingService();
            $service->createLead($landingId, $data);

            return $this->renderPartial('_lead_success');

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return $this->renderPartial('_lead_error', ['message' => 'Ошибка отправки. Попробуйте ещё раз.']);
        }
    }
}