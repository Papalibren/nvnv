<?php

namespace app\controllers\admin;

use Yii;
use app\models\RatingWeightConfig;
use app\models\PublicChallenge;
use app\models\Task;

class RatingController extends BaseAdminController
{

public function beforeAction($action)
{
    if (empty(Yii::$app->params['features']['rating'])) {
        Yii::$app->session->setFlash('error', 'Раздел временно отключён.');
        $this->redirect(['/admin/dashboard/index']);
        return false;
    }
    return parent::beforeAction($action);
}

public function actionWeights()
    {
        $this->view->title = 'Веса рейтинга';

        $config = RatingWeightConfig::current();

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $config->course_weight      = (int) $data['course_weight'];
            $config->tutoring_weight    = (int) $data['tutoring_weight'];
            $config->public_task_weight = (int) $data['public_task_weight'];
            $config->public_exam_weight = (int) $data['public_exam_weight'];
            $config->updated_at         = time();
            $config->save(false);

            Yii::$app->session->setFlash('success', 'Веса обновлены.');
            return $this->redirect(['/admin/rating/weights']);
        }

        return $this->render('weights', ['config' => $config]);
    }

    public function actionChallenges()
    {
        $this->view->title = 'Публичные задачи';

        $challenges = PublicChallenge::find()->orderBy('opens_at DESC')->all();

        return $this->render('challenges', ['challenges' => $challenges]);
    }

    public function actionCreateChallenge()
    {
        $this->view->title = 'Новая публичная задача';

        $tasks = Task::find()->where(['status' => 'published'])->orderBy('task_number')->all();
        $error = null;

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $challenge             = new PublicChallenge();
            $challenge->task_id    = (int) $data['task_id'];
            $challenge->title      = trim($data['title'] ?? '');
            $challenge->points     = (int) ($data['points'] ?? 20);
            $challenge->opens_at   = strtotime($data['opens_at']);
            $challenge->closes_at  = strtotime($data['closes_at']);
            $challenge->created_by = Yii::$app->user->id;
            $challenge->created_at = time();

            if ($challenge->title === '' || !$challenge->task_id) {
                $error = 'Заполните название и выберите задачу.';
            } elseif ($challenge->save()) {
                Yii::$app->session->setFlash('success', 'Публичная задача создана.');
                return $this->redirect(['/admin/rating/challenges']);
            } else {
                $error = implode(', ', $challenge->getFirstErrors());
            }
        }

        return $this->render('create-challenge', ['tasks' => $tasks, 'error' => $error]);
    }
}