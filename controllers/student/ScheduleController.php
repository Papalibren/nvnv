<?php

namespace app\controllers\student;

use yii\web\NotFoundHttpException;
use app\models\ClassSession;

class ScheduleController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'Мои занятия';
        $student = $this->getStudent();

        $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');

        $sessions = ClassSession::find()
            ->where(['or',
                ['student_id' => $student->id],
                ['in', 'group_id', $groupIds],
            ])
            ->orderBy('scheduled_at DESC')
            ->all();

        return $this->render('index', ['sessions' => $sessions]);
    }

    public function actionView(int $id)
    {
        $student  = $this->getStudent();
        $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');

        $session = ClassSession::find()
            ->where(['id' => $id])
            ->andWhere(['or',
                ['student_id' => $student->id],
                ['in', 'group_id', $groupIds],
            ])
            ->one();

        if (!$session) {
            throw new NotFoundHttpException('Занятие не найдено.');
        }

        $this->view->title = $session->title;

        $bookPages = $session->lesson ? $session->lesson->getBookPages()->with('chapter.section')->all() : [];

        // Найдём соответствующие записи ДЗ/экзамена именно этого ученика
        $homeworkStudent = null;
        if ($session->homework_id) {
            $homeworkStudent = \app\models\HomeworkStudent::findOne([
                'homework_id' => $session->homework_id,
                'student_id'  => $student->id,
            ]);
        }

        $examAttempt = null;
        if ($session->exam_id) {
            $examAttempt = \app\models\ExamAttempt::findOne([
                'exam_id'    => $session->exam_id,
                'student_id' => $student->id,
            ]);
        }

        return $this->render('view', [
            'session'         => $session,
            'bookPages'       => $bookPages,
            'homeworkStudent' => $homeworkStudent,
            'examAttempt'     => $examAttempt,
        ]);
    }
}