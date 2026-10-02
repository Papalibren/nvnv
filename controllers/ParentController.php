<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\services\TimelineService;

class ParentController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionView(string $token)
    {
        $student = User::findOne(['parent_share_token' => $token, 'role' => User::ROLE_STUDENT]);
        if (!$student) {
            throw new NotFoundHttpException('Ссылка недействительна.');
        }

        $this->view->title = 'История обучения — ' . $student->name;

        $items = (new TimelineService())->buildForStudent($student);

        return $this->render('timeline', ['student' => $student, 'items' => $items]);
    }
}