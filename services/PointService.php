<?php

namespace app\services;

use Yii;
use app\models\PointTransaction;

class PointService
{
    /**
     * Рассчитать баллы за задачу ДЗ
     */
    public function calcHomeworkPoints(
        int $maxPoints,
        bool $isCorrect,
        bool $isOverdue,
        int $attemptNumber
    ): int {
        if (!$isCorrect) return 0;

        $points = $maxPoints;

        // После дедлайна — коэффициент 0.5
        if ($isOverdue) {
            $points = (int) floor($points * 0.5);
        }

        // Вторая попытка — коэффициент 0.8
        if ($attemptNumber === 2) {
            $points = (int) floor($points * 0.8);
        }

        return max(0, $points);
    }

    /**
     * Начислить баллы
     */
    public function award(
        int $studentId,
        string $sourceType,
        int $sourceId,
        int $points,
        string $description = ''
    ): PointTransaction {
        $tx              = new PointTransaction();
        $tx->student_id  = $studentId;
        $tx->source_type = $sourceType;
        $tx->source_id   = $sourceId;
        $tx->points      = $points;
        $tx->description = $description;
        $tx->save();

        return $tx;
    }
}