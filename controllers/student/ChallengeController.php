<?php

namespace app\controllers\student;

use Yii;
use app\models\PublicChallenge;
use app\models\PublicChallengeAttempt;

class ChallengeController extends BaseStudentController
{
public function beforeAction($action)
{
    if (empty(Yii::$app->params['features']['publicChallenges'])) {
        Yii::$app->session->setFlash('error', 'Раздел временно недоступен.');
        $this->redirect(['/student/dashboard/index']);
        return false;
    }
    return parent::beforeAction($action);
}

public function actionIndex()
    {
        $this->view->title = 'Публичные задачи';

        $studentId = $this->getStudent()->id;

        $challenges = PublicChallenge::find()
            ->where(['<=', 'opens_at', time()])
            ->orderBy('opens_at DESC')
            ->limit(20)
            ->all();

        return $this->render('index', ['challenges' => $challenges, 'studentId' => $studentId]);
    }

    public function actionSubmit(int $id)
    {
        $challenge = PublicChallenge::findOne($id);
        $student   = $this->getStudent();

        if (!$challenge || !$challenge->isOpen()) {
            Yii::$app->session->setFlash('error', 'Задача сейчас недоступна.');
            return $this->redirect(['/student/challenge/index']);
        }

        $existing = $challenge->getAttemptFor($student->id);
        if ($existing) {
            return $this->redirect(['/student/challenge/index']);
        }

        $answer    = trim(Yii::$app->request->post('answer', ''));
        $isCorrect = $challenge->task->checkAnswer($answer);

        $attempt                = new PublicChallengeAttempt();
        $attempt->challenge_id  = $challenge->id;
        $attempt->student_id    = $student->id;
        $attempt->answer_text   = $answer;
        $attempt->is_correct    = $isCorrect;
        $attempt->points_earned = $isCorrect ? $challenge->points : 0;
        $attempt->submitted_at  = time();
        $attempt->save();

        if ($isCorrect) {
            (new \app\services\PointService())->award(
                $student->id, 'public_task', $challenge->id, $challenge->points,
                'Публичная задача: ' . $challenge->title
            );
        }

        Yii::$app->session->setFlash(
            $isCorrect ? 'success' : 'error',
            $isCorrect ? 'Верно! Начислено ' . $challenge->points . ' баллов.' : 'Неверно, попробуйте следующую задачу.'
        );

        return $this->redirect(['/student/challenge/index']);
    }
}