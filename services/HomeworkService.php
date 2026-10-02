<?php

namespace app\services;

use Yii;
use app\models\Homework;
use app\models\HomeworkTask;
use app\models\HomeworkStudent;
use app\models\HomeworkAnswer;
use app\models\Group;
use app\models\Task;

class HomeworkService
{
    /**
     * Получить ID задач которые уже были выданы ученику хоть раз
     */
    public function getAssignedTaskIds(int $studentId): array
    {
        return HomeworkAnswer::find()
            ->joinWith('homeworkTask')
            ->joinWith('homeworkStudent')
            ->where(['homework_student.student_id' => $studentId])
            ->select('homework_task.task_id')
            ->column();
    }

    /**
     * Получить задачи доступные для выдачи ученику (ещё не получал)
     */
    public function getAvailableTasksForStudent(int $studentId): array
    {
        $assignedIds = $this->getAssignedTaskIds($studentId);

        $query = Task::find()
            ->where(['status' => Task::STATUS_PUBLISHED])
            ->orderBy(['task_number' => SORT_ASC]);

        if (!empty($assignedIds)) {
            $query->andWhere(['not in', 'id', $assignedIds]);
        }

        return $query->all();
    }

    /**
     * Получить задачи уже выданные в конкретном ДЗ студенту
     * (для повторного просмотра — не блокируем, просто помечаем)
     */
    public function getTaskIdsAlreadyAssignedToStudent(int $studentId): array
    {
        return HomeworkStudent::find()
            ->joinWith('homework.homeworkTasks')
            ->where(['homework_student.student_id' => $studentId])
            ->select('homework_task.task_id')
            ->column();
    }

    /**
     * Создать ДЗ и сразу назначить ученикам группы (fan-out)
     */
    public function create(array $data, int $teacherId): Homework
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $homework              = new Homework();
            $homework->teacher_id  = $teacherId;
            $homework->group_id    = $data['group_id'] ?: null;
            $homework->lesson_id   = $data['lesson_id'] ?: null;
            $homework->title       = $data['title'];
            $homework->deadline_at = $data['deadline_at'] ?: null;
            $homework->status      = Homework::STATUS_PUBLISHED;
            $homework->description    = trim($data['description'] ?? '') ?: null;
            $homework->oral_questions  = trim($data['oral_questions'] ?? '') ?: null;

            if (!$homework->save()) {
                throw new \RuntimeException(implode(', ', $homework->getFirstErrors()));
            }

            // Добавляем задачи в ДЗ
            foreach ($data['tasks'] as $sortOrder => $taskData) {
                $ht              = new HomeworkTask();
                $ht->homework_id = $homework->id;
                $ht->task_id     = (int) $taskData['task_id'];
                $ht->max_points  = (int) ($taskData['max_points'] ?? 10);
                $ht->sort_order  = $sortOrder;
                $ht->save();
            }

            // Fan-out: создаём записи для каждого ученика группы
            if ($homework->group_id) {
                $this->fanOutToGroup($homework);
            }

            $transaction->commit();
            // Уведомляем учеников
            $notifService = new \app\services\NotificationService();
            $notifService->homeworkAssigned($homework->id);
            return $homework;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Fan-out — создаём HomeworkStudent для каждого ученика группы
     */
    public function fanOutToGroup(Homework $homework): void
    {
        $group = Group::findOne($homework->group_id);
        if (!$group) return;

        $students = $group->getStudents()->all();

        foreach ($students as $student) {
            // Проверяем нет ли уже записи
            $exists = HomeworkStudent::findOne([
                'homework_id' => $homework->id,
                'student_id'  => $student->id,
            ]);

            if (!$exists) {
                $hs              = new HomeworkStudent();
                $hs->homework_id = $homework->id;
                $hs->student_id  = $student->id;
                $hs->status      = HomeworkStudent::STATUS_ASSIGNED;
                $hs->assigned_at = time();
                $hs->save();
            }
        }
    }

    /**
     * Назначить ДЗ конкретному ученику (не через группу)
     */
    public function assignToStudent(Homework $homework, int $studentId): HomeworkStudent
    {
        $existing = HomeworkStudent::findOne([
            'homework_id' => $homework->id,
            'student_id'  => $studentId,
        ]);

        if ($existing) return $existing;

        $hs              = new HomeworkStudent();
        $hs->homework_id = $homework->id;
        $hs->student_id  = $studentId;
        $hs->status      = HomeworkStudent::STATUS_ASSIGNED;
        $hs->assigned_at = time();
        $hs->save();

        return $hs;
    }

    /**
     * Получить статистику по ДЗ для учителя
     */
    public function getHomeworkStats(Homework $homework): array
    {
        $total     = HomeworkStudent::find()->where(['homework_id' => $homework->id])->count();
        $submitted = HomeworkStudent::find()->where([
            'homework_id' => $homework->id,
            'status'      => [HomeworkStudent::STATUS_SUBMITTED, HomeworkStudent::STATUS_REVIEWED],
        ])->count();

        return [
            'total'     => (int) $total,
            'submitted' => (int) $submitted,
            'pending'   => (int) $total - (int) $submitted,
        ];
    }
}