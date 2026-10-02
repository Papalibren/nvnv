<?php

namespace app\controllers\student;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\HomeworkStudent;
use app\models\HomeworkAnswer;
use app\models\HomeworkTask;
use app\services\PointService;
use app\services\ActivityService;

class HomeworkController extends BaseStudentController
{
    public function actionIndex()
    {
        $this->view->title = 'Домашние задания';

        $student = $this->getStudent();

        $active = HomeworkStudent::find()
            ->joinWith('homework')
            ->where(['homework_student.student_id' => $student->id])
            ->andWhere(['homework_student.status' => [
                HomeworkStudent::STATUS_ASSIGNED,
                HomeworkStudent::STATUS_IN_PROGRESS,
            ]])
            ->all();

        // Убираем те что привязаны к ещё не проведённому занятию
        $active = array_filter($active, function ($hs) {
            $session = \app\models\ClassSession::findOne(['homework_id' => $hs->homework_id]);
            return !$session || $session->status === 'completed';
        });

        $done = HomeworkStudent::find()
            ->joinWith('homework')
            ->where(['homework_student.student_id' => $student->id])
            ->andWhere(['homework_student.status' => [
                HomeworkStudent::STATUS_SUBMITTED,
                HomeworkStudent::STATUS_REVIEWED,
            ]])
            ->orderBy(['homework_student.submitted_at' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('index', [
            'active' => $active,
            'done'   => $done,
        ]);
    }

    public function actionView(int $id)
    {
        $hs = $this->findHomeworkStudent($id);

        // Если ДЗ привязано к занятию — доступ только после того как оно проведено
        $session = \app\models\ClassSession::findOne(['homework_id' => $hs->homework_id]);
        if ($session && $session->status !== 'completed') {
            Yii::$app->session->setFlash('error', 'Это ДЗ откроется после проведения занятия.');
            return $this->redirect(['/student/schedule/index']);
        }

        $this->view->title = $hs->homework->title;

        // Если первый раз открывает — меняем статус
        if ($hs->status === HomeworkStudent::STATUS_ASSIGNED) {
            $hs->status = HomeworkStudent::STATUS_IN_PROGRESS;
            $hs->save(false);
        }

        // Уже нельзя редактировать
        $readonly = $hs->isSubmitted();

        // Загружаем задачи с ответами ученика
        $tasks   = $hs->homework->homeworkTasks;
        $answers = [];

        foreach ($hs->answers as $answer) {
            $answers[$answer->homework_task_id] = $answer;
        }

        return $this->render('view', [
            'hs'       => $hs,
            'tasks'    => $tasks,
            'answers'  => $answers,
            'readonly' => $readonly,
        ]);
    }

    /**
     * Автосохранение черновика (HTMX)
     */
    public function actionSaveDraft(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $hs = $this->findHomeworkStudent($id);

        $session = \app\models\ClassSession::findOne(['homework_id' => $hs->homework_id]);
        if ($session && $session->status !== 'completed') {
            return ['ok' => false, 'message' => 'Занятие ещё не проведено'];
        }

        if ($hs->isSubmitted()) {
            return ['ok' => false, 'message' => 'ДЗ уже сдано'];
        }

        $homeworkTaskId = (int) Yii::$app->request->post('homework_task_id');
        $answerText     = trim(Yii::$app->request->post('answer', ''));

        $answer = HomeworkAnswer::findOne([
            'homework_student_id' => $hs->id,
            'homework_task_id'    => $homeworkTaskId,
            'attempt_number'      => 1,
        ]);

        if (!$answer) {
            $answer                      = new HomeworkAnswer();
            $answer->homework_student_id = $hs->id;
            $answer->homework_task_id    = $homeworkTaskId;
            $answer->attempt_number      = 1;
        }

        $answer->answer_text = $answerText;
        $answer->save(false);

        return ['ok' => true];
    }

    /**
     * Отправка ДЗ
     */
    public function actionSubmit(int $id)
    {
        $hs = $this->findHomeworkStudent($id);

        $session = \app\models\ClassSession::findOne(['homework_id' => $hs->homework_id]);
        if ($session && $session->status !== 'completed') {
            Yii::$app->session->setFlash('error', 'Занятие ещё не проведено.');
            return $this->redirect(['/student/schedule/index']);
        }

        if ($hs->isSubmitted()) {
            Yii::$app->session->setFlash('error', 'ДЗ уже сдано.');
            return $this->redirect(['/student/homework/view', 'id' => $id]);
        }

        $homework     = $hs->homework;
        $isOverdue    = $homework->isOverdue();
        $pointService = new PointService();

        // Инициализируем до try чтобы были доступны в catch
        $correctCount = 0;
        $totalPoints  = 0;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($homework->homeworkTasks as $ht) {
                $answer = HomeworkAnswer::findOne([
                    'homework_student_id' => $hs->id,
                    'homework_task_id'    => $ht->id,
                    'attempt_number'      => 1,
                ]);

                if (!$answer) {
                    $answer                      = new HomeworkAnswer();
                    $answer->homework_student_id = $hs->id;
                    $answer->homework_task_id    = $ht->id;
                    $answer->attempt_number      = 1;
                    $answer->answer_text         = '';
                }

                $answer->correct_answer_snapshot = $ht->task->answer;
                $answer->submitted_at            = time();

                $isCorrect = $ht->task->checkAnswer($answer->answer_text);

                if ($isCorrect === null) {
                    // Развёрнутый ответ — не автопроверяется
                    $answer->is_correct          = null;
                    $answer->needs_manual_review = true;
                } else {
                    $answer->is_correct = $isCorrect;
                }

                if ($isCorrect) {
                    $correctCount++;
                }

$points = $isCorrect === null
    ? 0 // баллы за задачи с ручной проверкой учитель выставит сам
    : $pointService->calcHomeworkPoints($ht->max_points, $isCorrect, $isOverdue, 1);

                $answer->points_earned = $points;
                $totalPoints          += $points;
                $answer->save(false);

                if ($points > 0) {
                    $pointService->award(
                        $hs->student_id,
                        'homework',
                        $hs->id,
                        $points,
                        'ДЗ: ' . $homework->title . ', задание ' . $ht->task->task_number
                    );
                }
            }

            $hs->status       = HomeworkStudent::STATUS_SUBMITTED;
            $hs->submitted_at = time();
            $hs->save(false);

            // Уведомляем учителя
            $notifService = new \app\services\NotificationService();
            $notifService->homeworkSubmitted($hs->id);

            // Логируем активность
            \app\services\ActivityService::log(
                $hs->student_id,
                'homework_submit',
                'homework_student',
                $hs->id,
                [
                    'homework_title' => $homework->title,
                    'correct'        => $correctCount,
                    'points'         => $totalPoints,
                ]
            );

            // Обновляем прогресс роадмапа курса если это ДЗ из самоподготовки
            if ($homework->lesson_id) {
                $courseLesson = \app\models\CourseLesson::findOne(['lesson_id' => $homework->lesson_id]);
                if ($courseLesson) {
                    (new \app\services\CourseService())->recalcProgress($hs->student_id, $courseLesson->course_id);
                }
            }

            $transaction->commit();

            Yii::$app->session->setFlash('submitted', true);
            return $this->redirect(['/student/homework/result', 'id' => $id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::$app->session->setFlash('error', 'Ошибка при сдаче ДЗ: ' . $e->getMessage());
            return $this->redirect(['/student/homework/view', 'id' => $id]);
        }
    }

    /**
     * Повторная попытка одной задачи (один раз разрешено)
     */
    public function actionRetry(int $id)
    {
        $hs = $this->findHomeworkStudent($id);

        if ($hs->status !== HomeworkStudent::STATUS_SUBMITTED) {
            return $this->redirect(['/student/homework/view', 'id' => $id]);
        }

        $homeworkTaskId = (int) Yii::$app->request->post('homework_task_id');
        $answerText     = trim(Yii::$app->request->post('answer', ''));

        // Проверяем что второй попытки ещё не было
        $existing = HomeworkAnswer::findOne([
            'homework_student_id' => $hs->id,
            'homework_task_id'    => $homeworkTaskId,
            'attempt_number'      => 2,
        ]);

        if ($existing) {
            Yii::$app->session->setFlash('error', 'Вторая попытка уже использована.');
            return $this->redirect(['/student/homework/result', 'id' => $id]);
        }

        $ht         = HomeworkTask::findOne($homeworkTaskId);
        $isCorrect  = $ht->task->checkAnswer($answerText);
        $isOverdue  = $hs->homework->isOverdue();

        $pointService = new PointService();
        $points       = $pointService->calcHomeworkPoints(
            $ht->max_points,
            $isCorrect,
            $isOverdue,
            2 // attempt_number — коэффициент 0.8
        );

        $answer                      = new HomeworkAnswer();
        $answer->homework_student_id = $hs->id;
        $answer->homework_task_id    = $homeworkTaskId;
        $answer->attempt_number      = 2;
        $answer->answer_text         = $answerText;
        $answer->correct_answer_snapshot = $ht->task->answer;
        $answer->is_correct          = $isCorrect;
        $answer->points_earned       = $points;
        $answer->submitted_at        = time();
        $answer->save();

        if ($points > 0) {
            $pointService->award(
                $hs->student_id,
                'homework',
                $hs->id,
                $points,
                'Повторная попытка: ' . $hs->homework->title
            );
        }

        return $this->redirect(['/student/homework/result', 'id' => $id]);
    }

    /**
     * Страница результатов
     */
    public function actionResult(int $id)
    {
        $hs = $this->findHomeworkStudent($id);
        $this->view->title = 'Результат: ' . $hs->homework->title;

        if (!$hs->isSubmitted()) {
            return $this->redirect(['/student/homework/view', 'id' => $id]);
        }

        $answers = [];
        foreach ($hs->answers as $answer) {
            $tid = $answer->homework_task_id;
            if (!isset($answers[$tid]) || $answer->attempt_number > $answers[$tid]->attempt_number) {
                $answers[$tid] = $answer;
            }
        }

        $totalPoints  = array_sum(array_column($answers, 'points_earned'));
        $correctCount = count(array_filter($answers, fn($a) => $a->is_correct));

        return $this->render('result', [
            'hs'           => $hs,
            'answers'      => $answers,
            'totalPoints'  => $totalPoints,
            'correctCount' => $correctCount,
        ]);
    }

    private function findHomeworkStudent(int $id): HomeworkStudent
    {
        $hs = HomeworkStudent::findOne([
            'id'         => $id,
            'student_id' => $this->getStudent()->id,
        ]);

        if (!$hs) {
            throw new NotFoundHttpException('ДЗ не найдено.');
        }

        return $hs;
    }

    public function actionUploadFile(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $hs = $this->findHomeworkStudent($id);

        if ($hs->isSubmitted()) {
            return ['ok' => false, 'message' => 'ДЗ уже сдано'];
        }

        $file           = \yii\web\UploadedFile::getInstanceByName('file');
        $homeworkTaskId = (int) Yii::$app->request->post('homework_task_id');

        if (!$file) {
            return ['ok' => false, 'message' => 'Файл не выбран'];
        }

        // Лимит 20MB
        if ($file->size > 20 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'Файл слишком большой (макс. 20MB)'];
        }

        $path = Yii::$app->storage->save($file, 'submissions');

        $answer = HomeworkAnswer::findOne([
            'homework_student_id' => $hs->id,
            'homework_task_id'    => $homeworkTaskId,
            'attempt_number'      => 1,
        ]);

        if (!$answer) {
            $answer                      = new HomeworkAnswer();
            $answer->homework_student_id = $hs->id;
            $answer->homework_task_id    = $homeworkTaskId;
            $answer->attempt_number      = 1;
            $answer->answer_text         = '';
        }

        $answer->file_path = $path;
        $answer->save(false);

        return ['ok' => true, 'path' => $path];
    }
}
