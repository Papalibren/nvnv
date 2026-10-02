<?php

namespace app\services;

use Yii;
use yii\db\Query;
use app\models\User;
use app\models\RatingWeightConfig;
use app\models\StudentProgress;
use app\models\PublicChallengeAttempt;
use app\models\PublicChallenge;
use app\models\ExamAttempt;

class RatingService
{
    const PUBLIC_TASK_WINDOW_DAYS = 60;
    const PUBLIC_EXAM_SAMPLE      = 3; // среднее по последним N экзаменам

    /**
     * Составной балл ученика с разбивкой по компонентам
     */
    public function calcForStudent(User $student): array
    {
        $config = RatingWeightConfig::current();

        $components = [
            'course' => [
                'score'  => $this->courseScore($student->id),
                'weight' => $config->course_weight,
            ],
            'public_task' => [
                'score'  => $this->publicTaskScore($student->id),
                'weight' => $config->public_task_weight,
            ],
            'public_exam' => [
                'score'  => $this->publicExamScore($student->id),
                'weight' => $config->public_exam_weight,
            ],
        ];

        // Компонент "репетитор" доступен только tutored — иначе вес перераспределяется
        if ($student->isTutored()) {
            $components['tutoring'] = [
                'score'  => $this->tutoringScore($student->id),
                'weight' => $config->tutoring_weight,
            ];
        }

        $totalWeight = array_sum(array_column($components, 'weight'));
        if ($totalWeight === 0) {
            return ['total' => 0, 'components' => $components];
        }

        $total = 0;
        foreach ($components as $c) {
            $total += ($c['score'] * $c['weight']) / $totalWeight;
        }

        return [
            'total'      => round($total, 1),
            'components' => $components,
        ];
    }

    /**
     * Курс — средний % прогресса по всем зачислениям ученика
     */
    private function courseScore(int $studentId): float
    {
        $progresses = StudentProgress::find()->where(['student_id' => $studentId])->all();
        if (empty($progresses)) return 0;

        $sum = array_sum(array_column($progresses, 'percent'));
        return round($sum / count($progresses), 1);
    }

    /**
     * Репетитор — % успешности по ДЗ выданным именно учителем
     * (отличаем от авто-ДЗ курса по признаку lesson_id IS NULL)
     */
    private function tutoringScore(int $studentId): float
    {
        $row = (new Query())
            ->select(['SUM(ha.points_earned) as earned', 'SUM(ht.max_points) as possible'])
            ->from('homework_answer ha')
            ->innerJoin('homework_student hs', 'hs.id = ha.homework_student_id')
            ->innerJoin('homework_task ht', 'ht.id = ha.homework_task_id')
            ->innerJoin('homework h', 'h.id = hs.homework_id')
            ->where(['hs.student_id' => $studentId, 'h.lesson_id' => null])
            ->one();

        $possible = (float) ($row['possible'] ?? 0);
        if ($possible <= 0) return 0;

        return round(min(100, ((float) $row['earned'] / $possible) * 100), 1);
    }

    /**
     * Публичные задачи — заработано / доступно за последние 60 дней
     */
    private function publicTaskScore(int $studentId): float
    {
        $since = time() - self::PUBLIC_TASK_WINDOW_DAYS * 86400;

        $possible = (int) PublicChallenge::find()
            ->where(['>=', 'opens_at', $since])
            ->andWhere(['<=', 'opens_at', time()])
            ->sum('points');

        if ($possible <= 0) return 0;

        $earned = (int) PublicChallengeAttempt::find()
            ->innerJoin('public_challenge pc', 'pc.id = public_challenge_attempt.challenge_id')
            ->where(['public_challenge_attempt.student_id' => $studentId])
            ->andWhere(['>=', 'pc.opens_at', $since])
            ->sum('public_challenge_attempt.points_earned');

        return round(min(100, ($earned / $possible) * 100), 1);
    }

    /**
     * Публичные экзамены — средний % по последним N завершённым
     */
    private function publicExamScore(int $studentId): float
    {
        $attempts = ExamAttempt::find()
            ->innerJoin('exam e', 'e.id = exam_attempt.exam_id')
            ->where(['exam_attempt.student_id' => $studentId])
            ->andWhere(['e.is_proctored' => 1, 'e.is_public' => 1])
            ->andWhere(['not', ['exam_attempt.score_max' => null]])
            ->andWhere(['>', 'exam_attempt.score_max', 0])
            ->orderBy(['exam_attempt.submitted_at' => SORT_DESC])
            ->limit(self::PUBLIC_EXAM_SAMPLE)
            ->all();

        if (empty($attempts)) return 0;

        $sum = 0;
        foreach ($attempts as $a) {
            $sum += ($a->score_total / $a->score_max) * 100;
        }

        return round($sum / count($attempts), 1);
    }

    /**
     * Топ рейтинга — считаем для всех активных учеников (не более 300, чтобы не тормозило)
     */
    public function getLeaderboard(int $limit = 100): array
    {
        $students = User::find()
            ->where(['role' => User::ROLE_STUDENT, 'status' => User::STATUS_ACTIVE])
            ->limit(300)
            ->all();

        $rows = [];
        foreach ($students as $student) {
            $result = $this->calcForStudent($student);
            if ($result['total'] > 0) {
                $rows[] = [
                    'name'  => $student->display_name ?: ('Ученик #' . $student->id),
                    'total' => $result['total'],
                ];
            }
        }

        usort($rows, fn($a, $b) => $b['total'] <=> $a['total']);

        return array_slice($rows, 0, $limit);
    }
    /**
     * Перевод внутреннего % (0-100) в отображаемые баллы (0-1000)
     */
    public static function toDisplayPoints(float $percent): int
    {
        return (int) round($percent * 10);
    }
}