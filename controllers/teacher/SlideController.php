<?php

namespace app\controllers\teacher;

use yii\web\NotFoundHttpException;
use app\models\SlideDeck;
use app\helpers\ContentRenderer;

class SlideController extends BaseTeacherController
{
    /**
     * Учитель только запускает презентацию — не редактирует
     */
    public function actionPresent(int $id)
    {
        $deck = SlideDeck::findOne($id);
        if (!$deck) throw new NotFoundHttpException('Колода не найдена.');

        $this->layout = '@app/views/layouts/slide';
        $this->view->title = $deck->title;

        $slides = [];
        $notes  = [];
        foreach ($deck->slides as $slide) {
            $slides[] = ContentRenderer::render($slide->content ?? '');
            $notes[]  = $slide->notes ? ContentRenderer::render($slide->notes) : '';
        }

        return $this->render('present', [
            'slidesJson' => json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP),
            'notesJson'  => json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP),
        ]);
    }
}