<?php

namespace app\controllers\teacher;

use app\models\Homework;
use app\models\HomeworkStudent;
use app\models\Group;
use app\models\ClassSession;

class DashboardController extends BaseTeacherController
{
    public function actionIndex()
    {
        $this->view->title = 'Дашборд';

        $teacher = $this->getTeacher();

        $groups = Group::find()->where(['teacher_id' => $teacher->id])->all();

        $nextSessions = ClassSession::find()
            ->where(['teacher_id' => $teacher->id, 'status' => 'scheduled'])
            ->andWhere(['>=', 'scheduled_at', time()])
            ->orderBy('scheduled_at ASC')
            ->limit(5)
            ->all();

        // Реальный список сдач, ожидающих проверки — не просто число
        $pendingSubmissions = HomeworkStudent::find()
            ->joinWith(['homework', 'student'])
            ->where(['homework.teacher_id' => $teacher->id])
            ->andWhere(['homework_student.status' => HomeworkStudent::STATUS_SUBMITTED])
            ->orderBy(['homework_student.submitted_at' => SORT_ASC])
            ->limit(8)
            ->all();

        $totalHomeworks = Homework::find()->where(['teacher_id' => $teacher->id])->count();

        return $this->render('index', [
            'groups'              => $groups,
            'nextSessions'        => $nextSessions,
            'pendingSubmissions'  => $pendingSubmissions,
            'totalHomeworks'      => (int) $totalHomeworks,
        ]);
    }
}