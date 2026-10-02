<?php

namespace app\controllers\teacher;

use yii\web\NotFoundHttpException;
use app\models\Lesson;
use app\models\LessonTopic;

class LessonController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Уроки';

        $topics = LessonTopic::find()->orderBy('sort_order')->all();
        $lessonsByTopic = [];
        foreach ($topics as $topic) {
            $lessonsByTopic[$topic->id] = Lesson::find()
                ->where(['topic_id' => $topic->id])
                ->orderBy('sort_order')
                ->all();
        }

        $ungrouped = Lesson::find()
            ->where(['topic_id' => null])
            ->orderBy('sort_order')
            ->all();

        return $this->render('index', [
            'topics' => $topics, 'lessonsByTopic' => $lessonsByTopic, 'ungrouped' => $ungrouped,
        ]);
    }

    public function actionView(int $id)
    {
        $lesson = Lesson::findOne($id);
        if (!$lesson) throw new NotFoundHttpException('Урок не найден.');

        $this->view->title = $lesson->title;

        $bookPages = $lesson->getBookPages()->with('chapter.section')->all();
        $decks     = $lesson->getSlideDecks()->all();

        return $this->render('view', ['lesson' => $lesson, 'bookPages' => $bookPages, 'decks' => $decks]);
    }
}