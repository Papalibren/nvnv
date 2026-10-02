<?php

namespace app\controllers;

use yii\web\Controller;
use app\services\RatingService;
use app\models\User;
use Yii;

class RatingController extends Controller
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex()
    {
        if (empty(Yii::$app->params['features']['rating'])) {
            throw new \yii\web\NotFoundHttpException('Раздел временно недоступен.');
        }

        $this->view->title = 'Рейтинг учеников';
        $leaderboard = (new RatingService())->getLeaderboard();

        return $this->render('index', ['leaderboard' => $leaderboard]);
    }
}