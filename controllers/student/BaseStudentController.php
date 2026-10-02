<?php

namespace app\controllers\student;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

abstract class BaseStudentController extends Controller
{
    public $layout = '@app/views/layouts/cabinet';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'         => true,
                        'roles'         => ['@'],
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest
                                && Yii::$app->user->identity->isStudent();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->response->redirect(['/auth/login']);
                    }
                    throw new ForbiddenHttpException('Доступ запрещён.');
                },
            ],
        ];
    }

    /**
     * Текущий ученик
     */
    protected function getStudent(): \app\models\User
    {
        return Yii::$app->user->identity;
    }
}