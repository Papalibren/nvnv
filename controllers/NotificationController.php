<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use app\models\Notification;

class NotificationController extends Controller
{
    public $layout = '@app/views/layouts/cabinet';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    /**
     * Количество непрочитанных (для polling через HTMX)
     */
    public function actionCount()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        $count = Notification::countUnread(Yii::$app->user->id);

        if ($count === 0) return '';

        return '<span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full
                    bg-acid-pink text-white text-xs font-bold
                    flex items-center justify-center leading-none">'
            . ($count > 9 ? '9+' : $count)
            . '</span>';
    }

    /**
     * Список уведомлений (панель)
     */
public function actionPanel()
{
    $userId = Yii::$app->user->id;

    $notifications = Notification::find()
        ->where(['user_id' => $userId])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(8)
        ->all();

    return $this->renderPartial('_panel', ['notifications' => $notifications]);
}

public function actionMarkAllRead()
{
    Notification::markAllRead(Yii::$app->user->id);

    if (Yii::$app->request->headers->has('HX-Request')) {
        return $this->actionPanel();
    }
    return $this->redirect(['/notifications']);
}

public function actionClearAll()
{
    Notification::deleteAll(['user_id' => Yii::$app->user->id]);

    if (Yii::$app->request->headers->has('HX-Request')) {
        return $this->actionPanel();
    }
    return $this->redirect(['/notifications']);
}

public function actionIndex()
{
    $this->view->title = 'Уведомления';

    $notifications = Notification::find()
        ->where(['user_id' => Yii::$app->user->id])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(100)
        ->all();

    return $this->render('index', ['notifications' => $notifications]);
}
}