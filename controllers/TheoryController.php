<?php

namespace app\controllers;

use yii\web\Controller;

class TheoryController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionView(int $number)
    {
        return $this->render('view', ['number' => $number]);
    }
}