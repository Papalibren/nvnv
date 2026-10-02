<?php

namespace app\controllers\teacher;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\ClassSession;
use app\models\User;
use app\models\Group;
use app\models\Lesson;
use app\services\NotificationService;

class ScheduleController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Расписание';
        $teacher = $this->getTeacher();

        $upcoming = ClassSession::find()
            ->where(['teacher_id' => $teacher->id])
            ->andWhere(['status' => ClassSession::STATUS_SCHEDULED])
            ->orderBy('scheduled_at ASC')
            ->all();

        $past = ClassSession::find()
            ->where(['teacher_id' => $teacher->id])
            ->andWhere(['!=', 'status', ClassSession::STATUS_SCHEDULED])
            ->orderBy('scheduled_at DESC')
            ->limit(30)
            ->all();

        return $this->render('index', ['upcoming' => $upcoming, 'past' => $past]);
    }

    public function actionCreate()
    {
        $this->view->title = 'Новое занятие';
        $teacher = $this->getTeacher();

        $students = User::find()
            ->innerJoin('teacher_student ts', 'ts.student_id = user.id')
            ->where(['ts.teacher_id' => $teacher->id, 'user.status' => User::STATUS_ACTIVE])
            ->all();

        $groups = Group::find()->where(['teacher_id' => $teacher->id])->all();

        $lessons = Lesson::find()->orderBy('title')->all();

        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $studentId = $data['target_type'] === 'student' ? (int) $data['student_id'] : null;
            $groupId   = $data['target_type'] === 'group'   ? (int) $data['group_id']   : null;

            $dateTime = $data['scheduled_at'] ?? '';
            $timestamp = $dateTime ? strtotime($dateTime) : false;

            if (empty($data['title'])) {
                $error = 'Введите название занятия.';
            } elseif (!$studentId && !$groupId) {
                $error = 'Выберите ученика или группу.';
            } elseif (!$timestamp) {
                $error = 'Укажите корректную дату и время.';
            } else {
                $session                   = new ClassSession();
                $session->teacher_id       = $teacher->id;
                $session->student_id       = $studentId;
                $session->group_id         = $groupId;
                $session->lesson_id        = $data['lesson_id'] ?: null;
                $session->title            = trim($data['title']);
                $session->scheduled_at     = $timestamp;
                $session->duration_minutes = !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null;
                $session->status           = ClassSession::STATUS_SCHEDULED;
                $session->notes            = trim($data['notes'] ?? '') ?: null;
                $session->created_at       = time();

                if ($session->save()) {
                    $this->notifyStudents($session);
                    Yii::$app->session->setFlash('success', 'Занятие запланировано.');
                    return $this->redirect(['/teacher/schedule/view', 'id' => $session->id]);
                }
                $error = implode(', ', $session->getFirstErrors());
            }
        }

        return $this->render('create', [
            'students' => $students,
            'groups' => $groups,
            'lessons' => $lessons,
            'error' => $error,
        ]);
    }

    public function actionView(int $id)
    {
        $session = $this->findSession($id);
        $this->view->title = $session->title;

        return $this->render('view', ['session' => $session]);
    }

    public function actionComplete(int $id)
    {
        $session = $this->findSession($id);
        $session->status = ClassSession::STATUS_COMPLETED;
        $session->save(false);

        return $this->redirect(['/teacher/schedule/view', 'id' => $id]);
    }

    public function actionCancel(int $id)
    {
        $session = $this->findSession($id);
        $session->status = ClassSession::STATUS_CANCELLED;
        $session->save(false);

        return $this->redirect(['/teacher/schedule/view', 'id' => $id]);
    }

    private function notifyStudents(ClassSession $session): void
    {
        $notifService = new NotificationService();
        $studentIds = [];

        if ($session->student_id) {
            $studentIds[] = $session->student_id;
        } elseif ($session->group_id) {
            $studentIds = \yii\helpers\ArrayHelper::getColumn(
                $session->group->students,
                'id'
            );
        }

        foreach ($studentIds as $sid) {
            $notifService->create(
                $sid,
                'session_scheduled',
                'Новое занятие в расписании',
                $session->title . ' — ' . Yii::$app->formatter->asDatetime($session->scheduled_at, 'php:d.m.Y H:i'),
                'class_session',
                $session->id
            );
        }
    }

    public function actionUpdate(int $id)
    {
        $session = $this->findSession($id);
        $this->view->title = 'Изменить занятие';
        $teacher = $this->getTeacher();

        $lessons = \app\models\Lesson::find()->orderBy('title')->all();
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $dateTime  = $data['scheduled_at'] ?? '';
            $timestamp = $dateTime ? strtotime($dateTime) : false;

            if (empty($data['title'])) {
                $error = 'Введите название занятия.';
            } elseif (!$timestamp) {
                $error = 'Укажите корректную дату и время.';
            } else {
                $session->title            = trim($data['title']);
                $session->lesson_id        = $data['lesson_id'] ?: null;
                $session->scheduled_at     = $timestamp;
                $session->duration_minutes = !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null;
                $session->notes            = trim($data['notes'] ?? '') ?: null;
                $session->save(false);

                Yii::$app->session->setFlash('success', 'Занятие обновлено.');
                return $this->redirect(['/teacher/schedule/view', 'id' => $session->id]);
            }
        }

        return $this->render('update', ['session' => $session, 'lessons' => $lessons, 'error' => $error]);
    }

    public function actionReschedule(int $id)
    {
        $session = $this->findSession($id);

        if (Yii::$app->request->isPost) {
            $dateTime  = Yii::$app->request->post('scheduled_at', '');
            $timestamp = $dateTime ? strtotime($dateTime) : false;

            if ($timestamp) {
                $session->scheduled_at = $timestamp;
                $session->status       = ClassSession::STATUS_SCHEDULED;
                $session->save(false);

                (new NotificationService())->create(
                    $session->student_id ?: null,
                    'session_scheduled',
                    'Занятие перенесено',
                    $session->title . ' перенесено на ' . Yii::$app->formatter->asDatetime($timestamp, 'php:d.m.Y H:i'),
                    'class_session',
                    $session->id
                );

                Yii::$app->session->setFlash('success', 'Занятие перенесено.');
            }
        }

        return $this->redirect(['/teacher/schedule/view', 'id' => $session->id]);
    }

    private function findSession(int $id): ClassSession
    {
        $session = ClassSession::findOne(['id' => $id, 'teacher_id' => $this->getTeacher()->id]);
        if (!$session) throw new NotFoundHttpException('Занятие не найдено.');
        return $session;
    }
}
