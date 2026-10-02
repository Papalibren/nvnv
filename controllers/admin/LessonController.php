<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Lesson;
use app\models\LessonTopic;
use app\models\Group;
use app\models\BookPage;

class LessonController extends BaseAdminController
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
                ->with('teacher')
                ->all();
        }

        $ungrouped = Lesson::find()
            ->where(['topic_id' => null])
            ->orderBy('sort_order')
            ->with('teacher')
            ->all();

        return $this->render('index', [
            'topics'         => $topics,
            'lessonsByTopic' => $lessonsByTopic,
            'ungrouped'      => $ungrouped,
        ]);
    }

    public function actionCreateTopic()
    {
        $this->view->title = 'Новая тема';
        $error = null;

        if (Yii::$app->request->isPost) {
            $title = trim(Yii::$app->request->post('title', ''));

            if ($title === '') {
                $error = 'Введите название темы.';
            } else {
                $topic              = new LessonTopic();
                $topic->title       = $title;
                $topic->slug        = \yii\helpers\Inflector::slug($title);
                $topic->description = trim(Yii::$app->request->post('description', '')) ?: null;
                $topic->sort_order  = (int) LessonTopic::find()->max('sort_order') + 1;
                $topic->created_at  = time();

                if ($topic->save()) {
                    Yii::$app->session->setFlash('success', 'Тема создана.');
                    return $this->redirect(['/admin/lesson/index']);
                }
                $error = implode(', ', $topic->getFirstErrors());
            }
        }

        return $this->render('topic-form', ['error' => $error]);
    }

    public function actionMoveTopic(int $id, string $direction)
    {
        $topic = LessonTopic::findOne($id);
        if (!$topic) throw new NotFoundHttpException();

        $neighbor = LessonTopic::find()
            ->andWhere($direction === 'up'
                ? ['<', 'sort_order', $topic->sort_order]
                : ['>', 'sort_order', $topic->sort_order])
            ->orderBy($direction === 'up' ? 'sort_order DESC' : 'sort_order ASC')
            ->one();

        if ($neighbor) {
            $tmp = $topic->sort_order;
            $topic->sort_order = $neighbor->sort_order;
            $neighbor->sort_order = $tmp;
            $topic->save(false);
            $neighbor->save(false);
        }

        return $this->redirect(['/admin/lesson/index']);
    }

    public function actionMoveLesson(int $id, string $direction)
    {
        $lesson = Lesson::findOne($id);
        if (!$lesson) throw new NotFoundHttpException();

        $neighbor = Lesson::find()
            ->where(['topic_id' => $lesson->topic_id])
            ->andWhere($direction === 'up'
                ? ['<', 'sort_order', $lesson->sort_order]
                : ['>', 'sort_order', $lesson->sort_order])
            ->orderBy($direction === 'up' ? 'sort_order DESC' : 'sort_order ASC')
            ->one();

        if ($neighbor) {
            $tmp = $lesson->sort_order;
            $lesson->sort_order = $neighbor->sort_order;
            $neighbor->sort_order = $tmp;
            $lesson->save(false);
            $neighbor->save(false);
        }

        return $this->redirect(['/admin/lesson/index']);
    }

    public function actionCreate(?int $topicId = null)
    {
        $this->view->title = 'Новый урок';

        $topics    = LessonTopic::find()->orderBy('sort_order')->all();
        $groups    = Group::find()->all();
        $bookPages = BookPage::find()
            ->where(['not', ['published_at' => null]])
            ->with('chapter.section')
            ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data  = Yii::$app->request->post();
            $title = trim($data['title'] ?? '');
            $type  = $data['lesson_type'] ?? Lesson::TYPE_PRACTICE;

            if ($title === '') {
                $error = 'Введите название урока.';
            } else {
                $lesson               = new Lesson();
                $lesson->teacher_id   = Yii::$app->user->id;
                $lesson->topic_id     = $data['topic_id'] ?: null;
                $lesson->group_id     = $data['group_id'] ?: null;
                $lesson->title        = $title;
                $lesson->description  = trim($data['description'] ?? '') ?: null;
                $lesson->lesson_type  = $type;
                $lesson->info_content = $type === Lesson::TYPE_INFO ? ($data['info_content'] ?? '') : null;
                $lesson->status       = 'published';
                $lesson->created_at   = time();
                $lesson->sort_order   = $lesson->topic_id
                    ? (int) Lesson::find()->where(['topic_id' => $lesson->topic_id])->max('sort_order') + 1
                    : (int) Lesson::find()->where(['topic_id' => null])->max('sort_order') + 1;

                if ($lesson->save()) {
                    if ($type === Lesson::TYPE_PRACTICE) {
                        foreach ($data['book_page_ids'] ?? [] as $pageId) {
                            Yii::$app->db->createCommand()->insert('lesson_theory_link', [
                                'lesson_id'    => $lesson->id,
                                'content_type' => 'book_page',
                                'content_id'   => (int) $pageId,
                                'sort_order'   => 0,
                            ])->execute();
                        }
                    }
                    Yii::$app->session->setFlash('success', 'Урок создан.');
                    return $this->redirect(['/admin/lesson/view', 'id' => $lesson->id]);
                }
                $error = implode(', ', $lesson->getFirstErrors());
            }
        }

        return $this->render('lesson-form', [
            'lesson' => null,
            'topics' => $topics,
            'groups' => $groups,
            'bookPages' => $bookPages,
            'topicId' => $topicId,
            'error' => $error,
            'isNew' => true,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $lesson = $this->findLesson($id);
        $this->view->title = $lesson->title;

        $topics    = LessonTopic::find()->orderBy('sort_order')->all();
        $groups    = Group::find()->all();
        $bookPages = BookPage::find()
            ->where(['not', ['published_at' => null]])
            ->with('chapter.section')
            ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all();

        $selectedPageIds = (new \yii\db\Query())
            ->select('content_id')->from('lesson_theory_link')
            ->where(['lesson_id' => $lesson->id, 'content_type' => 'book_page'])
            ->column();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data  = Yii::$app->request->post();
            $title = trim($data['title'] ?? '');
            $type  = $data['lesson_type'] ?? Lesson::TYPE_PRACTICE;

            if ($title === '') {
                $error = 'Введите название.';
            } else {
                $lesson->title        = $title;
                $lesson->topic_id     = $data['topic_id'] ?: null;
                $lesson->group_id     = $data['group_id'] ?: null;
                $lesson->description  = trim($data['description'] ?? '') ?: null;
                $lesson->lesson_type  = $type;
                $lesson->info_content = $type === Lesson::TYPE_INFO ? ($data['info_content'] ?? '') : null;
                $lesson->save(false);

                Yii::$app->db->createCommand()
                    ->delete('lesson_theory_link', ['lesson_id' => $lesson->id])
                    ->execute();

                if ($type === Lesson::TYPE_PRACTICE) {
                    foreach ($data['book_page_ids'] ?? [] as $pageId) {
                        Yii::$app->db->createCommand()->insert('lesson_theory_link', [
                            'lesson_id'    => $lesson->id,
                            'content_type' => 'book_page',
                            'content_id'   => (int) $pageId,
                            'sort_order'   => 0,
                        ])->execute();
                    }
                }

                Yii::$app->session->setFlash('success', 'Урок обновлён.');
                return $this->redirect(['/admin/lesson/view', 'id' => $lesson->id]);
            }
        }

        return $this->render('lesson-form', [
            'lesson' => $lesson,
            'topics' => $topics,
            'groups' => $groups,
            'bookPages' => $bookPages,
            'selectedPageIds' => $selectedPageIds,
            'topicId' => $lesson->topic_id,
            'error' => $error,
            'isNew' => false,
        ]);
    }

    public function actionView(int $id)
    {
        $lesson = $this->findLesson($id);
        $this->view->title = $lesson->title;

        $bookPages = $lesson->getBookPages()->with('chapter.section')->all();
        $decks     = $lesson->getSlideDecks()->all();

        return $this->render('view', ['lesson' => $lesson, 'bookPages' => $bookPages, 'decks' => $decks]);
    }

    public function actionDelete(int $id)
    {
        $lesson = $this->findLesson($id);

        // Проверяем есть ли привязанные занятия — их нельзя оставить сиротами
        $hasSessions = \app\models\ClassSession::find()
            ->where(['lesson_id' => $lesson->id])
            ->exists();

        if ($hasSessions) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить урок — он используется в запланированных или проведённых занятиях.';
        }

        // Удаляем связанные слайды и их файлы из БД
        $decks = $lesson->getSlideDecks()->all();
        foreach ($decks as $deck) {
            \app\models\Slide::deleteAll(['deck_id' => $deck->id]);
            $deck->delete();
        }

        // Удаляем связи с теорией и отметки прочтения
        Yii::$app->db->createCommand()
            ->delete('lesson_theory_link', ['lesson_id' => $lesson->id])
            ->execute();

        \app\models\LessonReadMark::deleteAll(['lesson_id' => $lesson->id]);

        // Отвязываем из курсов (если courses когда-то включат заново)
        Yii::$app->db->createCommand()
            ->delete('course_lesson', ['lesson_id' => $lesson->id])
            ->execute();

        $lesson->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

    private function findLesson(int $id): Lesson
    {
        $lesson = Lesson::findOne($id);
        if (!$lesson) throw new NotFoundHttpException('Урок не найден.');
        return $lesson;
    }
}
