<?php

namespace app\services;

use app\models\User;
use app\models\ClassSession;
use app\models\HomeworkStudent;
use app\models\ExamAttempt;

class TimelineService
{
    /**
     * Собрать хронологическую ленту событий ученика
     */
    public function buildForStudent(User $student): array
    {
        $items = [];

        // Занятия — свои + групповые
        $groupIds = \yii\helpers\ArrayHelper::getColumn($student->getGroups()->all(), 'id');

$sessions = ClassSession::find()
    ->where(['or',
        ['student_id' => $student->id],
        ['in', 'group_id', $groupIds],
    ])
    ->orderBy('scheduled_at DESC')
    ->all();

        foreach ($sessions as $s) {
            $items[] = [
                'type'   => 'session',
                'date'   => $s->scheduled_at,
                'title'  => $s->title,
                'status' => $s->status,
                'meta'   => $s,
            ];
        }

        // Домашние задания
        $homeworks = HomeworkStudent::find()
            ->where(['student_id' => $student->id])
            ->with('homework')
            ->orderBy('assigned_at DESC')
            ->all();

        foreach ($homeworks as $hs) {
            $items[] = [
                'type'   => 'homework',
                'date'   => $hs->submitted_at ?: $hs->assigned_at,
                'title'  => $hs->homework->title ?? 'ДЗ',
                'status' => $hs->status,
                'meta'   => $hs,
            ];
        }

        // Экзамены
        $attempts = ExamAttempt::find()
            ->where(['student_id' => $student->id])
            ->with('exam')
            ->orderBy('started_at DESC')
            ->all();

        foreach ($attempts as $a) {
            $items[] = [
                'type'   => 'exam',
                'date'   => $a->submitted_at ?: $a->started_at,
                'title'  => $a->exam->title ?? 'Экзамен',
                'status' => $a->status,
                'meta'   => $a,
            ];
        }

        usort($items, fn($a, $b) => $b['date'] <=> $a['date']);

        return $items;
    }

    public function generateParentToken(User $student): string
    {
        if (!$student->parent_share_token) {
            $student->parent_share_token = \Yii::$app->security->generateRandomString(40);
            $student->save(false);
        }
        return $student->parent_share_token;
    }
}