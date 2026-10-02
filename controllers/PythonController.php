<?php

namespace app\controllers;

use yii\web\Controller;

class PythonController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        $this->view->title = 'Питон-песочница';
        return $this->render('index');
    }
}