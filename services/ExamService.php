<?php

namespace app\services;

use Yii;
use app\models\Exam;
use app\models\ExamTask;
use app\models\ExamAttempt;
use app\models\ExamAttemptAnswer;
use app\models\Task;

class ExamService
{
    /**
     * Создать экзамен с произвольным набором задач
     */
    public function create(array $data, int $teacherId): Exam
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $exam                   = new Exam();
            $exam->teacher_id       = $teacherId;
            $exam->group_id         = $data['group_id'] ?: null;
            $exam->title            = $data['title'];
            $exam->duration_minutes = $data['duration_minutes'] ?: null;
            $exam->is_full_scored   = (bool) ($data['is_full_scored'] ?? true);
            $exam->is_proctored     = (bool) ($data['is_proctored'] ?? false);
            $exam->is_public        = (bool) ($data['is_public'] ?? false);
            $exam->status           = Exam::STATUS_PUBLISHED;

            if (!$exam->save()) {
                throw new \RuntimeException(implode(', ', $exam->getFirstErrors()));
            }

            foreach ($data['task_ids'] as $i => $taskId) {
                $et             = new ExamTask();
                $et->exam_id    = $exam->id;
                $et->task_id    = (int) $taskId;
                $et->sort_order = $i;
                $et->save();
            }

            $transaction->commit();
            return $exam;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Начать попытку (или вернуть уже существующую)
     */
    public function startAttempt(Exam $exam, int $studentId): ExamAttempt
    {
        $existing = ExamAttempt::findOne(['exam_id' => $exam->id, 'student_id' => $studentId]);
        if ($existing) return $existing;

        $attempt             = new ExamAttempt();
        $attempt->exam_id    = $exam->id;
        $attempt->student_id = $studentId;
        $attempt->started_at = time();
        $attempt->status     = ExamAttempt::STATUS_IN_PROGRESS;
        $attempt->save();

        return $attempt;
    }

    /**
     * Сохранить ответ на один вопрос (черновик, во время прохождения)
     */
    public function saveAnswer(ExamAttempt $attempt, int $taskId, string $answerText): void
    {
        $answer = ExamAttemptAnswer::findOne(['attempt_id' => $attempt->id, 'task_id' => $taskId]);

        if (!$answer) {
            $answer             = new ExamAttemptAnswer();
            $answer->attempt_id = $attempt->id;
            $answer->task_id    = $taskId;
        }

        $answer->student_answer = $answerText;
        $answer->answered_at    = time();
        $answer->save(false);
    }

    /**
     * Завершить попытку — подсчитать баллы, снэпшот ответов
     */
    public function submit(ExamAttempt $attempt, bool $expired = false): void
    {
        if ($attempt->isFinished()) return;

        $exam       = $attempt->exam;
        $examTasks  = $exam->examTasks;
        $totalMax   = 0;
        $totalScore = 0;

        foreach ($examTasks as $et) {
            $answer = ExamAttemptAnswer::findOne(['attempt_id' => $attempt->id, 'task_id' => $et->task_id]);

            if (!$answer) {
                $answer             = new ExamAttemptAnswer();
                $answer->attempt_id = $attempt->id;
                $answer->task_id    = $et->task_id;
                $answer->student_answer = '';
            }

            $answer->correct_answer_snapshot = $et->task->answer;
            $isCorrect = $et->task->checkAnswer($answer->student_answer);

            if ($isCorrect === null) {
                $answer->is_correct          = null;
                $answer->needs_manual_review = true;
            } else {
                $answer->is_correct = $isCorrect;
            }

            // Вес задачи — на полном экзамене (0-100) вес пропорционален количеству заданий
            $taskMax = $exam->is_full_scored
                ? (int) round(100 / max(1, count($examTasks)))
                : 1;

            $answer->points_earned = $isCorrect === true ? $taskMax : 0;
            $answer->answered_at   = $answer->answered_at ?: time();
            $answer->save(false);

            $totalMax   += $taskMax;
            $totalScore += $answer->points_earned;
        }

        $attempt->status       = $expired ? ExamAttempt::STATUS_EXPIRED : ExamAttempt::STATUS_SUBMITTED;
        $attempt->submitted_at = time();
        $attempt->score_total  = $totalScore;
        $attempt->score_max    = $totalMax;
        $attempt->save(false);

        // Начисляем баллы — только если проходной (proctored) экзамен даёт РЕЙТИНГОВЫЕ баллы,
        // иначе баллы всё равно начисляются, но как "практика" (не участвует в /rating)
        if ($totalScore > 0) {
            $pointService = new PointService();
            $pointService->award(
                $attempt->student_id,
                'exam',
                $attempt->id,
                $totalScore,
                'Экзамен: ' . $exam->title . ($exam->is_proctored ? ' (защищённый режим)' : '')
            );
        }

        // Пересчитываем прогресс роадмапа, если этот экзамен — чья-то контрольная точка
        $courseLessons = \app\models\CourseLesson::find()
            ->where(['checkpoint_exam_id' => $exam->id])
            ->all();

        foreach ($courseLessons as $cl) {
            (new \app\services\CourseService())->recalcProgress($attempt->student_id, $cl->course_id);
        }

        // Уведомление
        $notifService = new NotificationService();
        $notifService->create(
            $exam->teacher_id,
            'exam_finished',
            'Ученик завершил экзамен',
            $attempt->student->name . ' завершил «' . $exam->title . '»',
            'exam_attempt',
            $attempt->id
        );
    }

    /**
     * Крон: закрыть просроченные попытки
     */
    public function expireOverdueAttempts(): int
    {
        $count = 0;

        $inProgress = ExamAttempt::find()
            ->where(['status' => ExamAttempt::STATUS_IN_PROGRESS])
            ->all();

        foreach ($inProgress as $attempt) {
            if ($attempt->isTimeExpired()) {
                $this->submit($attempt, true);
                $count++;
            }
        }

        return $count;
    }
}