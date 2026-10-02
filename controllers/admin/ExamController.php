<?php

namespace app\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Exam;
use app\models\Task;
use app\services\ExamService;

class ExamController extends BaseAdminController
{
    public function actionIndex()
    {
        $this->view->title = 'Публичные экзамены';

        $exams = Exam::find()->where(['is_public' => true])->orderBy('created_at DESC')->all();

        return $this->render('index', ['exams' => $exams]);
    }

    public function actionCreate()
    {
        $this->view->title = 'Новый публичный экзамен';

        $allTasks = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data    = Yii::$app->request->post();
            $taskIds = $data['task_ids'] ?? [];

            if (empty($data['title'])) {
                $error = 'Введите название.';
            } elseif (empty($taskIds)) {
                $error = 'Выберите задачи.';
            } else {
                $service = new ExamService();
                $exam    = $service->create([
                    'title'            => $data['title'],
                    'group_id'         => null,
                    'duration_minutes' => !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
                    'is_full_scored'   => isset($data['is_full_scored']),
                    'is_proctored'     => isset($data['is_proctored']),
                    'is_public'        => true,
                    'task_ids'         => $taskIds,
                ], Yii::$app->user->id);

                Yii::$app->session->setFlash('success', 'Публичный экзамен создан.');
                return $this->redirect(['/admin/exam/index']);
            }
        }

        return $this->render('create', ['allTasks' => $allTasks, 'error' => $error]);
    }

    public function actionAssignCheckpoint()
    {
        $this->view->title = 'Контрольные точки курса';

        $lessons = \app\models\CourseLesson::find()
            ->with(['lesson', 'checkpointExam'])
            ->orderBy('sort_order')
            ->all();

        $publicExams = Exam::find()->where(['is_public' => true])->all();

        if (Yii::$app->request->isPost) {
            $lessonId = (int) Yii::$app->request->post('course_lesson_id');
            $examId   = Yii::$app->request->post('exam_id');

            $cl = \app\models\CourseLesson::findOne($lessonId);
            if ($cl) {
                $cl->checkpoint_exam_id = $examId ?: null;
                $cl->save(false);
            }

            Yii::$app->session->setFlash('success', 'Контрольная точка обновлена.');
            return $this->redirect(['/admin/exam/assign-checkpoint']);
        }

        return $this->render('assign-checkpoint', ['lessons' => $lessons, 'publicExams' => $publicExams]);
    }

    public function actionDelete(int $id)
    {
        $exam = Exam::findOne($id);
        if (!$exam) throw new NotFoundHttpException('Экзамен не найден.');

        if (!empty($exam->attempts)) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить — есть прохождения. Вместо удаления снимите его с публикации.';
        }

        // Проверяем не привязан ли как контрольная точка курса
        $usedAsCheckpoint = \app\models\CourseLesson::find()
            ->where(['checkpoint_exam_id' => $exam->id])
            ->exists();

        if ($usedAsCheckpoint) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить — экзамен используется как контрольная точка курса. Сначала отвяжите его.';
        }

        // Проверяем не привязан ли к занятию
        $usedInSession = \app\models\ClassSession::find()
            ->where(['exam_id' => $exam->id])
            ->exists();

        if ($usedInSession) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить — экзамен привязан к занятию в расписании.';
        }

        Yii::$app->db->createCommand()->delete('exam_task', ['exam_id' => $exam->id])->execute();
        $exam->delete();

        Yii::$app->response->statusCode = 200;
        return '';
    }

        public function actionAll()
    {
        $this->view->title = 'Все экзамены';

        $exams = Exam::find()->orderBy('created_at DESC')->with('teacher')->all();

        return $this->render('all', ['exams' => $exams]);
    }
}
