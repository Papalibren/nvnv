<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\SlideDeck;
use app\models\Slide;
use app\models\Lesson;
use app\helpers\ContentRenderer;

class SlideController extends BaseAdminController
{
    public function actionIndex()
    {
        $this->view->title = 'Слайды';

        $decks = SlideDeck::find()->orderBy('created_at DESC')->with(['lesson', 'teacher'])->all();

        return $this->render('index', ['decks' => $decks]);
    }

    public function actionCreate(int $lessonId)
    {
        $lesson = Lesson::findOne($lessonId);
        if (!$lesson) throw new NotFoundHttpException('Урок не найден.');

        $deck             = new SlideDeck();
        $deck->lesson_id  = $lesson->id;
        $deck->teacher_id = Yii::$app->user->id;
        $deck->title      = $lesson->title;
        $deck->status     = 'draft';
        $deck->created_at = time();
        $deck->save(false);

        $slide             = new Slide();
        $slide->deck_id    = $deck->id;
        $slide->sort_order = 0;
        $slide->content    = "# {$lesson->title}\n\nНачните заполнять слайд...";
        $slide->created_at = time();
        $slide->save(false);

        return $this->redirect(['/admin/slide/edit', 'id' => $deck->id]);
    }

    public function actionEdit(int $id)
    {
        $deck = $this->findDeck($id);
        $this->view->title = 'Слайды: ' . $deck->title;

        if (Yii::$app->request->isPost) {
            $deck->title  = trim(Yii::$app->request->post('title', $deck->title));
            $deck->status = Yii::$app->request->post('status', $deck->status);
            $deck->save(false);
            Yii::$app->session->setFlash('success', 'Колода обновлена.');
            return $this->redirect(['/admin/slide/edit', 'id' => $deck->id]);
        }

        return $this->render('edit', ['deck' => $deck, 'slides' => $deck->slides]);
    }

    public function actionAddSlide(int $id)
    {
        $deck = $this->findDeck($id);
        $maxOrder = (int) Slide::find()->where(['deck_id' => $deck->id])->max('sort_order');

        $slide             = new Slide();
        $slide->deck_id    = $deck->id;
        $slide->sort_order = $maxOrder + 1;
        $slide->content    = '';
        $slide->created_at = time();
        $slide->save(false);

        return $this->redirect(['/admin/slide/edit', 'id' => $deck->id]);
    }

    public function actionUpdateSlide(int $id)
    {
        $slide = $this->findSlide($id);

        $slide->content = Yii::$app->request->post('content', $slide->content);
        $slide->notes   = Yii::$app->request->post('notes', $slide->notes);
        $slide->save(false);

        Yii::$app->session->setFlash('success', 'Слайд сохранён.');
        return $this->redirect(['/admin/slide/edit', 'id' => $slide->deck_id]);
    }

    public function actionDeleteSlide(int $id)
    {
        $slide = $this->findSlide($id);
        $slide->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

    public function actionMoveSlide(int $id, string $direction)
    {
        $slide = $this->findSlide($id);

        $neighbor = Slide::find()
            ->where(['deck_id' => $slide->deck_id])
            ->andWhere($direction === 'up'
                ? ['<', 'sort_order', $slide->sort_order]
                : ['>', 'sort_order', $slide->sort_order])
            ->orderBy($direction === 'up' ? 'sort_order DESC' : 'sort_order ASC')
            ->one();

        if ($neighbor) {
            $tmp = $slide->sort_order;
            $slide->sort_order = $neighbor->sort_order;
            $neighbor->sort_order = $tmp;
            $slide->save(false);
            $neighbor->save(false);
        }

        return $this->redirect(['/admin/slide/edit', 'id' => $slide->deck_id]);
    }

    public function actionDeleteDeck(int $id)
    {
        $deck     = $this->findDeck($id);
        $lessonId = $deck->lesson_id;
        $deck->delete();

        return $this->redirect(['/admin/lesson/view', 'id' => $lessonId]);
    }

    private function findDeck(int $id): SlideDeck
    {
        $deck = SlideDeck::findOne($id);
        if (!$deck) throw new NotFoundHttpException('Колода не найдена.');
        return $deck;
    }

    private function findSlide(int $id): Slide
    {
        $slide = Slide::findOne($id);
        if (!$slide) throw new NotFoundHttpException('Слайд не найден.');
        return $slide;
    }
}