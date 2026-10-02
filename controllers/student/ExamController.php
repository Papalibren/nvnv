<?php

namespace app\controllers\student;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\Exam;
use app\models\ExamAttempt;
use app\services\ExamService;

class ExamController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'Экзамены';

        $student = $this->getStudent();

        if ($student->isTutored()) {
            $teacher  = $student->getTeacher()->one();
            $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');

            $exams = Exam::find()
                ->where(['status' => Exam::STATUS_PUBLISHED])
                ->andWhere([
                    'or',
                    ['in', 'group_id', $groupIds],
                    ['and', ['group_id' => null], ['teacher_id' => $teacher->id]],
                ])
                ->orderBy('created_at DESC')
                ->all();
        } else {
            $exams = Exam::find()
                ->where(['status' => Exam::STATUS_PUBLISHED, 'is_public' => true])
                ->orderBy('created_at DESC')
                ->all();
        }
        
        $exams = array_filter($exams, function ($exam) {
            $session = \app\models\ClassSession::findOne(['exam_id' => $exam->id]);
            return !$session || $session->status === 'completed';
        });
        return $this->render('index', ['exams' => $exams, 'studentId' => $student->id]);
    }

    public function actionView(int $id)
    {
        $exam    = $this->findExam($id);
        $student = $this->getStudent();

        $session = \app\models\ClassSession::findOne(['exam_id' => $exam->id, 'student_id' => $student->id]);
    if (!$session) {
        // Экзамен мог быть привязан к групповому занятию
        $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');
        $session  = \app\models\ClassSession::findOne(['exam_id' => $exam->id]);
        if ($session && $session->group_id && !in_array($session->group_id, $groupIds)) {
            $session = null;
        }
    }

    if ($session && $session->status !== 'completed') {
        Yii::$app->session->setFlash('error', 'Этот экзамен откроется после проведения занятия.');
        return $this->redirect(['/student/schedule/index']);
    }

        $attempt = $exam->getStudentAttempt($student->id);

        if ($attempt && $attempt->isFinished()) {
            return $this->redirect(['/student/exam/result', 'id' => $attempt->id]);
        }

        if (!$attempt) {
            $service = new ExamService();
            $attempt = $service->startAttempt($exam, $student->id);
        }

        $this->view->title = $exam->title;

        $answers = [];
        foreach ($attempt->answers as $a) {
            $answers[$a->task_id] = $a;
        }

        return $this->render('view', [
            'exam'     => $exam,
            'attempt'  => $attempt,
            'answers'  => $answers,
        ]);
    }

    /**
     * Автосохранение ответа (HTMX)
     */
    public function actionAnswer(int $id)
    {
        $attempt = $this->findOwnAttempt($id);

        $session = \app\models\ClassSession::findOne(['exam_id' => $attempt->exam_id]);
    if ($session && $session->status !== 'completed') {
        Yii::$app->response->statusCode = 403;
        return '';
    }

        if (!$attempt->isInProgress()) {
            Yii::$app->response->statusCode = 409;
            return '';
        }

        $taskId = (int) Yii::$app->request->post('task_id');
        $text   = trim(Yii::$app->request->post('answer', ''));

        (new ExamService())->saveAnswer($attempt, $taskId, $text);

        Yii::$app->response->statusCode = 200;
        return '';
    }

    /**
     * Приём метрик античита (fullscreen exit / focus lost)
     */
    public function actionHeartbeat(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $attempt = $this->findOwnAttempt($id);

        if (!$attempt->isInProgress()) {
            return ['ok' => false];
        }

        $event = Yii::$app->request->post('event');

        if ($event === 'fullscreen_exit') {
            $attempt->fullscreen_exits++;
        } elseif ($event === 'focus_lost') {
            $attempt->focus_lost_count++;
        }
        $attempt->save(false);

        return [
            'ok'               => true,
            'remaining'        => $attempt->getRemainingSeconds(),
            'expired'          => $attempt->isTimeExpired(),
        ];
    }

    public function actionSubmit(int $id)
    {
        $attempt = $this->findOwnAttempt($id);

        if ($attempt->isInProgress()) {
            (new ExamService())->submit($attempt, false);
        }

        return $this->redirect(['/student/exam/result', 'id' => $attempt->id]);
    }

    public function actionResult(int $id)
    {
        $attempt = $this->findOwnAttempt($id);

            $session = \app\models\ClassSession::findOne(['exam_id' => $attempt->exam_id]);
    if ($session && $session->status !== 'completed') {
        Yii::$app->session->setFlash('error', 'Занятие ещё не проведено.');
        return $this->redirect(['/student/schedule/index']);
    }

        $this->view->title = 'Результат: ' . $attempt->exam->title;

        if ($attempt->isInProgress()) {
            (new ExamService())->submit($attempt, false);
        }

        return $this->render('result', ['attempt' => $attempt]);
    }

    private function findExam(int $id): Exam
    {
        $exam = Exam::findOne(['id' => $id, 'status' => Exam::STATUS_PUBLISHED]);
        if (!$exam) throw new NotFoundHttpException('Экзамен не найден.');
        return $exam;
    }

    private function findOwnAttempt(int $id): ExamAttempt
    {
        $attempt = ExamAttempt::findOne(['id' => $id, 'student_id' => $this->getStudent()->id]);
        if (!$attempt) throw new NotFoundHttpException('Попытка не найдена.');
        return $attempt;
    }
}
