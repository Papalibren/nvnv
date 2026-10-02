<?php

namespace app\services;

use Yii;
use app\models\Notification;
use app\models\User;

class NotificationService
{
    /**
     * Создать уведомление в ЛК
     */
    public function create(
        int    $userId,
        string $type,
        string $title,
        string $body,
        string $relatedType = null,
        int    $relatedId   = null
    ): Notification {
        $n               = new Notification();
        $n->user_id      = $userId;
        $n->type         = $type;
        $n->channel      = Notification::CHANNEL_SITE;
        $n->title        = $title;
        $n->body         = $body;
        $n->is_read      = false;
        $n->related_type = $relatedType;
        $n->related_id   = $relatedId;
        $n->save();

        return $n;
    }

    /**
     * Уведомить о новом ДЗ всех учеников
     */
    public function homeworkAssigned(int $homeworkId): void
    {
        $homework = \app\models\Homework::findOne($homeworkId);
        if (!$homework) return;

        $students = \app\models\HomeworkStudent::find()
            ->where(['homework_id' => $homeworkId])
            ->all();

        foreach ($students as $hs) {
            $this->create(
                $hs->student_id,
                Notification::TYPE_HOMEWORK_ASSIGNED,
                'Новое домашнее задание',
                'Вам выдано ДЗ: «' . $homework->title . '»',
                'homework',
                $hs->id
            );
        }
    }

    /**
     * Уведомить учителя о сдаче ДЗ
     */
    public function homeworkSubmitted(int $homeworkStudentId): void
    {
        $hs = \app\models\HomeworkStudent::findOne($homeworkStudentId);
        if (!$hs) return;

        $homework = $hs->homework;
        $student  = $hs->student;

        $this->create(
            $homework->teacher_id,
            Notification::TYPE_HOMEWORK_SUBMITTED,
            'Ученик сдал ДЗ',
            $student->name . ' сдал ДЗ «' . $homework->title . '»',
            'homework',
            $hs->id
        );
    }
}