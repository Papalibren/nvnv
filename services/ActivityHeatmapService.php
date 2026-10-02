<?php

namespace app\services;

use app\models\HomeworkStudent;

class ActivityHeatmapService
{
    const STATUS_NONE       = 'none';
    const STATUS_MISSED     = 'missed';
    const STATUS_ATTEMPTED  = 'attempted';
    const STATUS_RETRY_DONE = 'retry_done';
    const STATUS_DONE       = 'done';

    /**
     * Построить сетку недель для тепловой карты (как на GitHub)
     */
public function buildForStudent(int $studentId, int $weeksBack = 18): array
{
    $today = strtotime('today');
    $startDow = (int) date('N', $today); // 1=Пн ... 7=Вс
    $gridStart = $today - $weeksBack * 7 * 86400 - ($startDow - 1) * 86400;

    $records = HomeworkStudent::find()
        ->where(['student_id' => $studentId])
        ->with(['homework', 'answers'])
        ->all();

    $dayStatuses = [];

    foreach ($records as $hs) {
        // День для отметки: если сдано — день сдачи; если нет — день дедлайна (для отметки "не сделано")
        if ($hs->isSubmitted()) {
            $day = $hs->submitted_at ?: $hs->assigned_at;
        } else {
            $day = $hs->homework->deadline_at ?? null;
            if (!$day) continue; // без дедлайна и без сдачи — нечего отмечать
        }

        if ($day < $gridStart || $day > $today + 86400) continue;

        $status = $this->resolveStatus($hs, $day, $today);
        if ($status === self::STATUS_NONE) continue;

        $key = date('Y-m-d', $day);
        $dayStatuses[$key] = $this->worseStatus($dayStatuses[$key] ?? null, $status);
    }

    $weeks = [];
    $cursor = $gridStart;
    while ($cursor <= $today) {
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $key = date('Y-m-d', $cursor);
            $week[] = ['date' => $key, 'status' => $dayStatuses[$key] ?? self::STATUS_NONE];
            $cursor += 86400;
        }
        $weeks[] = $week;
    }

    return $weeks;
}

private function resolveStatus(HomeworkStudent $hs, int $day, int $today): string
{
    if (!$hs->isSubmitted()) {
        return self::STATUS_MISSED;
    }

    $byTask = [];
    foreach ($hs->answers as $a) {
        $tid = $a->homework_task_id;
        if (!isset($byTask[$tid]) || $a->attempt_number > $byTask[$tid]->attempt_number) {
            $byTask[$tid] = $a;
        }
    }

    if (empty($byTask)) return self::STATUS_NONE;

    $allCorrect   = true;
    $anyRetryUsed = false;

    foreach ($byTask as $a) {
        if ($a->is_correct) {
            if ($a->attempt_number > 1) $anyRetryUsed = true;
        } else {
            $allCorrect = false;
        }
    }

    if ($allCorrect && !$anyRetryUsed) return self::STATUS_DONE;
    if ($allCorrect && $anyRetryUsed)  return self::STATUS_RETRY_DONE;

    return self::STATUS_ATTEMPTED;
}

    private function worseStatus(?string $a, string $b): string
    {
        $rank = [
            self::STATUS_MISSED     => 3,
            self::STATUS_ATTEMPTED  => 2,
            self::STATUS_RETRY_DONE => 1,
            self::STATUS_DONE       => 0,
        ];
        if ($a === null) return $b;
        return ($rank[$a] ?? -1) >= ($rank[$b] ?? -1) ? $a : $b;
    }
}