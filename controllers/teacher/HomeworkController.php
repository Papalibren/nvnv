<?php

namespace app\controllers\teacher;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\Homework;
use app\models\HomeworkStudent;
use app\models\HomeworkAnswer;
use app\models\Group;
use app\models\Task;
use app\services\HomeworkService;

class HomeworkController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Домашние задания';

        $homeworks = Homework::find()
            ->where(['teacher_id' => $this->getTeacher()->id])
            ->orderBy('created_at DESC')
            ->all();

        return $this->render('index', ['homeworks' => $homeworks]);
    }

    public function actionCreate(?int $sessionId = null)
    {
        $this->view->title = 'Новое ДЗ';

        $teacher  = $this->getTeacher();
        $groups   = Group::find()->where(['teacher_id' => $teacher->id])->all();
        $allTasks = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data        = Yii::$app->request->post();
            $taskIds     = $data['task_ids']   ?? [];
            $maxPoints   = $data['max_points'] ?? [];
            $deadlineRaw = $data['deadline_at'] ?? '';

            if (empty($data['title'])) {
                $error = 'Введите название ДЗ.';
            } elseif (empty($taskIds)) {
                $error = 'Выберите хотя бы одну задачу.';
            } else {
                $tasks = [];
                foreach ($taskIds as $taskId) {
                    $tasks[] = [
                        'task_id'    => (int) $taskId,
                        'max_points' => (int) ($maxPoints[$taskId] ?? 10),
                    ];
                }

                try {
                    $service = new HomeworkService();
                    $hw      = $service->create([
                        'title'       => $data['title'],
                        'group_id'    => $data['group_id'] ?: null,
                        'lesson_id'   => null,
                        'deadline_at' => $deadlineRaw ? strtotime($deadlineRaw) : null,
                        'tasks'       => $tasks,
                        'description'    => trim($data['description'] ?? '') ?: null,
                        'oral_questions' => trim($data['oral_questions'] ?? '') ?: null
                    ], $teacher->id);

                    if ($sessionId) {
                        $session = \app\models\ClassSession::findOne(['id' => $sessionId, 'teacher_id' => $teacher->id]);
                        if ($session) {
                            $session->homework_id = $hw->id;
                            $session->save(false);
                        }
                    }

                    Yii::$app->session->setFlash('success', 'ДЗ создано и назначено.');
                    return $sessionId
                        ? $this->redirect(['/teacher/schedule/view', 'id' => $sessionId])
                        : $this->redirect(['/teacher/homework/view', 'id' => $hw->id]);
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
        $homework = $this->findHomework($id);
        $this->view->title = $homework->title;

        $service = new HomeworkService();
        $stats   = $service->getHomeworkStats($homework);

        $submissions = HomeworkStudent::find()
            ->where(['homework_id' => $homework->id])
            ->with(['student', 'answers'])
            ->orderBy('submitted_at DESC')
            ->all();

        return $this->render('view', [
            'homework'    => $homework,
            'stats'       => $stats,
            'submissions' => $submissions,
        ]);
    }

    private function findHomework(int $id): Homework
    {
        $hw = Homework::findOne(['id' => $id, 'teacher_id' => $this->getTeacher()->id]);
        if (!$hw) {
            throw new NotFoundHttpException('ДЗ не найдено.');
        }
        return $hw;
    }
    /**
     * Возвращает список ID задач уже выданных ученикам группы
     * Используется HTMX при смене группы в форме ДЗ
     */
    public function actionGroupTaskIds()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $groupId = (int) Yii::$app->request->get('group_id');
        $teacher = $this->getTeacher();

        if (!$groupId) {
            return [];
        }

        $group = Group::findOne(['id' => $groupId, 'teacher_id' => $teacher->id]);
        if (!$group) {
            return [];
        }

        $service = new HomeworkService();
        $ids     = [];

        foreach ($group->students as $student) {
            $ids = array_merge(
                $ids,
                $service->getTaskIdsAlreadyAssignedToStudent($student->id)
            );
        }

        return array_values(array_unique($ids));
    }

    /**
     * Список сдач конкретного ДЗ — с фильтром "на проверке"
     */
    public function actionSubmissions(int $id)
    {
        $homework = $this->findHomework($id);
        $this->view->title = 'Проверка: ' . $homework->title;

        $submissions = HomeworkStudent::find()
            ->where(['homework_id' => $homework->id])
            ->andWhere(['in', 'status', [HomeworkStudent::STATUS_SUBMITTED, HomeworkStudent::STATUS_REVIEWED]])
            ->with(['student', 'answers'])
            ->orderBy(['status' => SORT_ASC, 'submitted_at' => SORT_ASC])
            ->all();

        return $this->render('submissions', ['homework' => $homework, 'submissions' => $submissions]);
    }

    /**
     * Сохранить комментарии учителя к ответам и отметить проверенным
     */
    public function actionReview(int $id)
    {
        $hs = HomeworkStudent::findOne($id);
        if (!$hs || $hs->homework->teacher_id !== $this->getTeacher()->id) {
            throw new NotFoundHttpException('Не найдено.');
        }

        if (Yii::$app->request->isPost) {
            $comments = Yii::$app->request->post('comment', []);

            foreach ($hs->answers as $answer) {
                if (isset($comments[$answer->id])) {
                    $answer->teacher_comment = trim($comments[$answer->id]) ?: null;
                    $answer->save(false);
                }
            }

            $hs->status = HomeworkStudent::STATUS_REVIEWED;
            $hs->save(false);

            Yii::$app->session->setFlash('success', 'Отмечено проверенным.');
            return $this->redirect(['/teacher/homework/submissions', 'id' => $hs->homework_id]);
        }

        return $this->redirect(['/teacher/homework/submissions', 'id' => $hs->homework_id]);
    }
}
