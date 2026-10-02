<?php

namespace app\controllers\teacher;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

abstract class BaseTeacherController extends Controller
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
                                && Yii::$app->user->identity->isAdminOrTeacher();
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

    protected function getTeacher(): \app\models\User
    {
        return Yii::$app->user->identity;
    }
}