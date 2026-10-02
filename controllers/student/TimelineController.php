<?php

namespace app\controllers\student;

use app\services\TimelineService;

class TimelineController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'История обучения';
        $student = $this->getStudent();

        $items = (new TimelineService())->buildForStudent($student);
        $shareToken = $student->parent_share_token;

        return $this->render('index', ['items' => $items, 'shareToken' => $shareToken, 'studentId' => $student->id]);
    }

    public function actionGenerateShareLink()
    {
        $student = $this->getStudent();
        (new TimelineService())->generateParentToken($student);

        return $this->redirect(['/student/timeline/index']);
    }
}