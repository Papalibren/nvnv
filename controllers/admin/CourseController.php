<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Course;
use app\models\CourseLesson;
use app\models\Lesson;
use app\models\BookPage;

class CourseController extends BaseAdminController
{

public function beforeAction($action)
{
    if (empty(Yii::$app->params['features']['courses'])) {
        Yii::$app->session->setFlash('error', 'Раздел временно отключён.');
        $this->redirect(['/admin/dashboard/index']);
        return false;
    }
    return parent::beforeAction($action);
}

public function actionIndex()
    {
        $this->view->title = 'Курсы';

        $courses = Course::find()->orderBy('created_at DESC')->all();

        return $this->render('index', ['courses' => $courses]);
    }

    public function actionCreate()
    {
        $this->view->title = 'Новый курс';
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $course              = new Course();
            $course->title       = trim($data['title'] ?? '');
            $course->slug        = \yii\helpers\Inflector::slug($course->title);
            $course->description = trim($data['description'] ?? '') ?: null;
            $course->price       = 0;
            $course->status      = 'published';
            $course->created_by  = Yii::$app->user->id;
            $course->created_at  = time();
            $course->updated_at  = time();

            if ($course->title === '') {
                $error = 'Введите название курса.';
            } elseif ($course->save()) {
                Yii::$app->session->setFlash('success', 'Курс создан.');
                return $this->redirect(['/admin/course/view', 'id' => $course->id]);
            } else {
                $error = implode(', ', $course->getFirstErrors());
            }
        }

        return $this->render('create', ['error' => $error]);
    }

    public function actionView(int $id)
    {
        $course = $this->findCourse($id);
        $this->view->title = $course->title;

        $lessons = CourseLesson::find()
            ->where(['course_id' => $course->id])
            ->orderBy('sort_order')
            ->with(['lesson', 'checkpointExam'])
            ->all();

        return $this->render('view', ['course' => $course, 'lessons' => $lessons]);
    }

public function actionCreateLesson(int $courseId)
{
    $course = $this->findCourse($courseId);
    $this->view->title = 'Новая тема курса';

    $bookPages = BookPage::find()
        ->where(['not', ['published_at' => null]])
        ->with('chapter.section')
        ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
        ->all();

    $error = null;

    if (Yii::$app->request->isPost) {
        $data       = Yii::$app->request->post();
        $title      = trim($data['title'] ?? '');
        $lessonType = $data['lesson_type'] ?? Lesson::TYPE_PRACTICE;

        if ($title === '') {
            $error = 'Введите название темы.';
        } else {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $lesson              = new Lesson();
                $lesson->teacher_id  = Yii::$app->user->id;
                $lesson->title       = $title;
                $lesson->lesson_type = $lessonType;
                $lesson->info_content = $lessonType === Lesson::TYPE_INFO
                    ? ($data['info_content'] ?? '')
                    : null;
                $lesson->status      = 'published';
                $lesson->created_at  = time();
                $lesson->save();

                $maxOrder = (int) CourseLesson::find()
                    ->where(['course_id' => $course->id])
                    ->max('sort_order');

                $cl             = new CourseLesson();
                $cl->course_id  = $course->id;
                $cl->lesson_id  = $lesson->id;
                $cl->sort_order = $maxOrder + 1;
                $cl->save();

                // Теория привязывается только у практических тем
                if ($lessonType === Lesson::TYPE_PRACTICE) {
                    foreach ($data['book_page_ids'] ?? [] as $pageId) {
                        Yii::$app->db->createCommand()->insert('lesson_theory_link', [
                            'lesson_id'    => $lesson->id,
                            'content_type' => 'book_page',
                            'content_id'   => (int) $pageId,
                            'sort_order'   => 0,
                        ])->execute();
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Тема добавлена в курс.');
                return $this->redirect(['/admin/course/view', 'id' => $course->id]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                $error = $e->getMessage();
            }
        }
    }

    return $this->render('lesson-form', [
        'course'    => $course,
        'lesson'    => null,
        'bookPages' => $bookPages,
        'error'     => $error,
        'isNew'     => true,
    ]);
}

public function actionUpdateLesson(int $id)
{
    $cl = CourseLesson::findOne($id);
    if (!$cl) throw new NotFoundHttpException('Тема не найдена.');

    $course = $cl->course;
    $this->view->title = 'Тема: ' . ($cl->lesson->title ?? '');

    $bookPages = BookPage::find()
        ->where(['not', ['published_at' => null]])
        ->with('chapter.section')
        ->orderBy(['chapter_id' => SORT_ASC, 'sort_order' => SORT_ASC])
        ->all();

    $selectedPageIds = (new \yii\db\Query())
        ->select('content_id')
        ->from('lesson_theory_link')
        ->where(['lesson_id' => $cl->lesson_id, 'content_type' => 'book_page'])
        ->column();

    $error = null;

    if (Yii::$app->request->isPost) {
        $data       = Yii::$app->request->post();
        $title      = trim($data['title'] ?? '');
        $lessonType = $data['lesson_type'] ?? Lesson::TYPE_PRACTICE;

        if ($title === '') {
            $error = 'Введите название.';
        } else {
            $lesson               = $cl->lesson;
            $lesson->title        = $title;
            $lesson->lesson_type  = $lessonType;
            $lesson->info_content = $lessonType === Lesson::TYPE_INFO
                ? ($data['info_content'] ?? '')
                : null;
            $lesson->save(false);

            Yii::$app->db->createCommand()
                ->delete('lesson_theory_link', ['lesson_id' => $cl->lesson_id])
                ->execute();

            if ($lessonType === Lesson::TYPE_PRACTICE) {
                foreach ($data['book_page_ids'] ?? [] as $pageId) {
                    Yii::$app->db->createCommand()->insert('lesson_theory_link', [
                        'lesson_id'    => $cl->lesson_id,
                        'content_type' => 'book_page',
                        'content_id'   => (int) $pageId,
                        'sort_order'   => 0,
                    ])->execute();
                }
            }

            Yii::$app->session->setFlash('success', 'Тема обновлена.');
            return $this->redirect(['/admin/course/view', 'id' => $course->id]);
        }
    }

    return $this->render('lesson-form', [
        'course'          => $course,
        'lesson'          => $cl,
        'bookPages'       => $bookPages,
        'selectedPageIds' => $selectedPageIds,
        'error'           => $error,
        'isNew'           => false,
    ]);
}

    public function actionDeleteLesson(int $id)
    {
        $cl = CourseLesson::findOne($id);
        if (!$cl) throw new NotFoundHttpException();

        $lessonId = $cl->lesson_id;
        $cl->delete();

        Yii::$app->db->createCommand()->delete('lesson_theory_link', ['lesson_id' => $lessonId])->execute();
        Yii::$app->db->createCommand()->delete('lesson', ['id' => $lessonId])->execute();

        Yii::$app->response->statusCode = 200;
        return '';
    }

    private function findCourse(int $id): Course
    {
        $course = Course::findOne($id);
        if (!$course) throw new NotFoundHttpException('Курс не найден.');
        return $course;
    }

    public function actionMoveLesson(int $id, string $direction)
    {
        $cl = CourseLesson::findOne($id);
        if (!$cl) throw new NotFoundHttpException();

        $neighbor = CourseLesson::find()
            ->where(['course_id' => $cl->course_id])
            ->andWhere($direction === 'up'
                ? ['<', 'sort_order', $cl->sort_order]
                : ['>', 'sort_order', $cl->sort_order])
            ->orderBy($direction === 'up' ? 'sort_order DESC' : 'sort_order ASC')
            ->one();

        if ($neighbor) {
            $tmp = $cl->sort_order;
            $cl->sort_order = $neighbor->sort_order;
            $neighbor->sort_order = $tmp;
            $cl->save(false);
            $neighbor->save(false);
        }

        return $this->redirect(['/admin/course/view', 'id' => $cl->course_id]);
    }
    public function actionProcessUnlocks()
    {
        $count = (new \app\services\CourseService())->processUnlocks();
        Yii::$app->session->setFlash('success', "Обработано разблокировок: {$count}");
        return $this->redirect(['/admin/course/index']);
    }
}
