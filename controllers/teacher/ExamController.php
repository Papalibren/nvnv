<?php

namespace app\controllers\teacher;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Exam;
use app\models\Group;
use app\models\Task;
use app\services\ExamService;

class ExamController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Экзамены';

        $exams = Exam::find()
            ->where(['teacher_id' => $this->getTeacher()->id])
            ->orderBy('created_at DESC')
            ->all();

        return $this->render('index', ['exams' => $exams]);
    }

public function actionCreate(?int $sessionId = null)
{
        $this->view->title = 'Новый экзамен';

        $teacher  = $this->getTeacher();
        $groups   = Group::find()->where(['teacher_id' => $teacher->id])->all();
        $allTasks = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data    = Yii::$app->request->post();
            $taskIds = $data['task_ids'] ?? [];

            if (empty($data['title'])) {
                $error = 'Введите название экзамена.';
            } elseif (empty($taskIds)) {
                $error = 'Выберите хотя бы одну задачу.';
            } else {
                try {
                    $service = new ExamService();
                    $exam    = $service->create([
                        'title'            => $data['title'],
                        'group_id'         => $data['group_id'] ?: null,
                        'duration_minutes' => !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
                        'is_full_scored'   => isset($data['is_full_scored']),
                        'is_public'        => false,
                        'is_proctored'     => isset($data['is_proctored']),
                        'task_ids'         => $taskIds,
                    ], $teacher->id);

                    if ($sessionId) {
    $session = \app\models\ClassSession::findOne(['id' => $sessionId, 'teacher_id' => $teacher->id]);
    if ($session) {
        $session->exam_id = $exam->id;
        $session->save(false);
    }
}
Yii::$app->session->setFlash('success', 'Экзамен создан.');
return $sessionId
    ? $this->redirect(['/teacher/schedule/view', 'id' => $sessionId])
    : $this->redirect(['/teacher/exam/view', 'id' => $exam->id]);
    
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return $this->render('create', [
            'groups'   => $groups,
            'allTasks' => $allTasks,
            'error'    => $error,
        ]);
    }

    public function actionView(int $id)
    {
        $exam = $this->findExam($id);
        $this->view->title = $exam->title;

        $attempts = $exam->attempts;

        return $this->render('view', ['exam' => $exam, 'attempts' => $attempts]);
    }

    public function actionUpdate(int $id)
    {
        $exam = $this->findExam($id);
        $this->view->title = 'Экзамен: ' . $exam->title;

        // Редактировать состав задач можно только если ещё нет ни одной попытки
        $hasAttempts = !empty($exam->attempts);

        $teacher  = $this->getTeacher();
        $groups   = Group::find()->where(['teacher_id' => $teacher->id])->all();
        $allTasks = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $selectedTaskIds = array_column($exam->examTasks, 'task_id');
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $exam->title            = trim($data['title'] ?? $exam->title);
            $exam->group_id         = $data['group_id'] ?: null;
            $exam->duration_minutes = !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null;
            $exam->is_full_scored   = isset($data['is_full_scored']);
            $exam->is_proctored     = isset($data['is_proctored']);

            if ($exam->title === '') {
                $error = 'Введите название.';
            } elseif ($exam->save()) {

                // Состав задач меняем только если ещё не начинали проходить
                if (!$hasAttempts && !empty($data['task_ids'])) {
                    Yii::$app->db->createCommand()->delete('exam_task', ['exam_id' => $exam->id])->execute();
                    foreach ($data['task_ids'] as $i => $taskId) {
                        Yii::$app->db->createCommand()->insert('exam_task', [
                            'exam_id'    => $exam->id,
                            'task_id'    => (int) $taskId,
                            'sort_order' => $i,
                        ])->execute();
                    }
                }

                Yii::$app->session->setFlash('success', 'Экзамен обновлён.');
                return $this->redirect(['/teacher/exam/view', 'id' => $exam->id]);
            } else {
                $error = implode(', ', $exam->getFirstErrors());
            }
        }

        return $this->render('update', [
            'exam'            => $exam,
            'groups'          => $groups,
            'allTasks'        => $allTasks,
            'selectedTaskIds' => $selectedTaskIds,
            'hasAttempts'     => $hasAttempts,
            'error'           => $error,
        ]);
    }

    public function actionDelete(int $id)
    {
        $exam = $this->findExam($id);

        // Защита данных — если кто-то уже проходил, полное удаление запрещено
        if (!empty($exam->attempts)) {
            Yii::$app->response->statusCode = 422;
            return 'Нельзя удалить экзамен — есть попытки прохождения. Вместо этого снимите его с публикации.';
        }

        $exam->delete();
        Yii::$app->response->statusCode = 200;
        return '';
    }

    public function actionArchive(int $id)
    {
        $exam = $this->findExam($id);
        $exam->status = $exam->status === Exam::STATUS_FINISHED
            ? Exam::STATUS_PUBLISHED
            : Exam::STATUS_FINISHED;
        $exam->save(false);

        return $this->renderPartial('_status_badge', ['exam' => $exam]);
    }

    public function actionAttempt(int $id)
    {
        $attempt = \app\models\ExamAttempt::findOne($id);
        if (!$attempt || $attempt->exam->teacher_id !== $this->getTeacher()->id) {
            throw new NotFoundHttpException('Попытка не найдена.');
        }

        $this->view->title = 'Результат: ' . $attempt->student->name;

        return $this->render('attempt', ['attempt' => $attempt]);
    }

    private function findExam(int $id): Exam
    {
        $exam = Exam::findOne(['id' => $id, 'teacher_id' => $this->getTeacher()->id]);
        if (!$exam) throw new NotFoundHttpException('Экзамен не найден.');
        return $exam;
    }
}
