<?php

namespace app\services;

use Yii;
use app\models\Course;
use app\models\CourseEnrollment;
use app\models\CourseLesson;
use app\models\CourseScheduleItem;
use app\models\StudentProgress;
use app\models\Homework;
use app\models\HomeworkTask;
use app\models\HomeworkStudent;
use app\models\Task;

class CourseService
{
    const DEFAULT_COURSE_SLUG = 'samostoyatelnaya-podgotovka-ege';
    const MIN_MONTHS = 1;
    const MAX_MONTHS = 10;

    /**
     * Зачислить ученика на курс с выбранным сроком и построить расписание
     */
    public function enroll(int $studentId, int $durationMonths, ?string $courseSlug = null): CourseEnrollment
    {
        $durationMonths = max(self::MIN_MONTHS, min(self::MAX_MONTHS, $durationMonths));

        $course = Course::findOne(['slug' => $courseSlug ?? self::DEFAULT_COURSE_SLUG]);
        if (!$course) {
            throw new \RuntimeException('Курс не найден.');
        }

        $existing = CourseEnrollment::findOne(['course_id' => $course->id, 'student_id' => $studentId]);
        if ($existing) return $existing;

        $now         = time();
        $totalDays   = $durationMonths * 30;
        $targetEndAt = $now + $totalDays * 86400;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $enrollment                   = new CourseEnrollment();
            $enrollment->course_id        = $course->id;
            $enrollment->student_id       = $studentId;
            $enrollment->enrolled_at      = $now;
            $enrollment->duration_months  = $durationMonths;
            $enrollment->target_end_at    = $targetEndAt;
            $enrollment->duration_changed = false;
            $enrollment->payment_status   = 'free';
            $enrollment->save();

            $this->buildSchedule($enrollment, $now, $totalDays);

            $progress                    = new StudentProgress();
            $progress->student_id        = $studentId;
            $progress->course_id         = $course->id;
            $progress->lessons_completed = 0;
            $progress->lessons_total     = $course->getCourseLessons()->count();
            $progress->percent           = 0;
            $progress->last_activity_at  = $now;
            $progress->save();

            $transaction->commit();
            return $enrollment;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Построить расписание разблокировки уроков равномерно на весь срок
     */
    private function buildSchedule(CourseEnrollment $enrollment, int $startAt, int $totalDays): void
    {
        $lessons = CourseLesson::find()
            ->where(['course_id' => $enrollment->course_id])
            ->orderBy('sort_order')
            ->all();

        $count = count($lessons);
        if ($count === 0) return;

        foreach ($lessons as $i => $lesson) {
            // Первый урок открыт сразу, остальные — равномерно распределены
            $dayOffset = (int) floor(($i / $count) * $totalDays);
            $unlockAt  = $startAt + $dayOffset * 86400;

            $item                    = new CourseScheduleItem();
            $item->enrollment_id     = $enrollment->id;
            $item->course_lesson_id  = $lesson->id;
            $item->unlock_at         = $unlockAt;
            $item->save();
        }
    }

    /**
     * Сменить срок прохождения — разрешено один раз, пересчитывает оставшиеся уроки
     */
    public function changeDuration(CourseEnrollment $enrollment, int $newDurationMonths): void
    {
        if ($enrollment->duration_changed) {
            throw new \RuntimeException('Срок можно изменить только один раз.');
        }

        $newDurationMonths = max(self::MIN_MONTHS, min(self::MAX_MONTHS, $newDurationMonths));
        $now = time();

        // Уже открытые уроки не трогаем — пересчитываем только будущие
        $items = CourseScheduleItem::find()
            ->where(['enrollment_id' => $enrollment->id])
            ->andWhere(['>', 'unlock_at', $now])
            ->orderBy('unlock_at')
            ->all();

        if (!empty($items)) {
            $remainingDays = max(1, $newDurationMonths * 30 - (int) floor(($now - $enrollment->enrolled_at) / 86400));
            $count = count($items);

            foreach ($items as $i => $item) {
                $dayOffset = (int) floor(($i / $count) * $remainingDays);
                $item->unlock_at = $now + $dayOffset * 86400;
                $item->save(false);
            }
        }

        $enrollment->duration_months  = $newDurationMonths;
        $enrollment->target_end_at    = $enrollment->enrolled_at + $newDurationMonths * 30 * 86400;
        $enrollment->duration_changed = true;
        $enrollment->save(false);
    }

    /**
     * Крон: разблокировать уроки чей срок наступил — создать персональное ДЗ
     */
    public function processUnlocks(): int
    {
        $now   = time();
        $count = 0;

        $items = CourseScheduleItem::find()
            ->where(['<=', 'unlock_at', $now])
            ->andWhere(['homework_id' => null])
            ->all();

        foreach ($items as $item) {
            $this->createHomeworkForScheduleItem($item);
            $count++;
        }

        return $count;
    }

    /**
     * Создать персональное ДЗ ученику под конкретный урок расписания
     */
    private function createHomeworkForScheduleItem(CourseScheduleItem $item): void
    {
        $lesson     = $item->courseLesson->lesson ?? null;
        $enrollment = $item->enrollment;

        if (!$lesson || !$enrollment) return;

        // Информационные темы не получают ДЗ — ученик отмечает их сам
        if ($lesson->isInfo()) return;

        // Берём задачи привязанные к теории урока (через lesson_theory_link -> book_page -> task_theory_link)
        $bookPageIds = \yii\helpers\ArrayHelper::getColumn(
            \app\models\LessonTheoryLink::find()
                ->where(['lesson_id' => $lesson->id, 'content_type' => 'book_page'])
                ->all(),
            'content_id'
        );

        $taskIds = [];
        if ($bookPageIds) {
            $taskIds = (new \yii\db\Query())
                ->select('task_id')
                ->from('task_theory_link')
                ->where(['book_page_id' => $bookPageIds])
                ->column();
        }

        if (empty($taskIds)) return;

        $admin = \app\models\User::find()->where(['role' => 'admin'])->one();
        if (!$admin) return;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $homework              = new Homework();
            $homework->teacher_id  = $admin->id;
            $homework->group_id    = null;
            $homework->lesson_id   = $lesson->id;
            $homework->title       = $lesson->title;
            $homework->deadline_at = null; // без жёсткого дедлайна в самоподготовке
            $homework->status      = Homework::STATUS_PUBLISHED;
            $homework->save();

            foreach (array_slice($taskIds, 0, 10) as $i => $taskId) {
                $task = Task::findOne($taskId);
                if (!$task) continue;

                $ht              = new HomeworkTask();
                $ht->homework_id = $homework->id;
                $ht->task_id     = $taskId;
                $ht->max_points  = $task->difficulty * 10;
                $ht->sort_order  = $i;
                $ht->save();
            }

            $hs              = new HomeworkStudent();
            $hs->homework_id = $homework->id;
            $hs->student_id  = $enrollment->student_id;
            $hs->status      = HomeworkStudent::STATUS_ASSIGNED;
            $hs->assigned_at = time();
            $hs->save();

            $item->homework_id = $homework->id;
            $item->save(false);

            (new NotificationService())->create(
                $enrollment->student_id,
                'homework_assigned',
                'Открылась новая тема курса',
                'Тема «' . $lesson->title . '» теперь доступна',
                'homework',
                $hs->id
            );

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка создания ДЗ роадмапа: ' . $e->getMessage());
        }
    }

    /**
     * Пересчитать прогресс ученика (вызывается после сдачи ДЗ)
     */
public function recalcProgress(int $studentId, int $courseId): void
{
    $enrollment = CourseEnrollment::findOne(['student_id' => $studentId, 'course_id' => $courseId]);
    if (!$enrollment) return;

    $items = CourseScheduleItem::find()
        ->where(['enrollment_id' => $enrollment->id])
        ->with('courseLesson.lesson')
        ->all();

    $completed = 0;

    foreach ($items as $item) {
        if (!$item->isUnlocked()) {
            continue;
        }

        $lesson = $item->courseLesson->lesson;

        if ($lesson && $lesson->isInfo()) {
            // Информационная тема — достаточно отметки "прочитано"
            $lessonDone = $lesson->isReadBy($studentId);
        } else {
            $requiresHomework = $item->homework_id !== null;
            $requiresExam     = $item->courseLesson->isCheckpoint();

            $homeworkOk = true;
            if ($requiresHomework) {
                $hs = HomeworkStudent::findOne([
                    'homework_id' => $item->homework_id,
                    'student_id'  => $studentId,
                ]);
                $homeworkOk = $hs && $hs->isSubmitted();
            }

            $examOk = true;
            if ($requiresExam) {
                $attempt = \app\models\ExamAttempt::findOne([
                    'exam_id'    => $item->courseLesson->checkpoint_exam_id,
                    'student_id' => $studentId,
                ]);
                $examOk = $attempt && $attempt->isFinished();
            }

            $lessonDone = $homeworkOk && $examOk;
        }

        if ($lessonDone) {
            if (!$item->completed_at) {
                $item->completed_at = time();
                $item->save(false);
            }
            $completed++;
        } elseif ($item->completed_at) {
            $item->completed_at = null;
            $item->save(false);
        }
    }

    $progress = StudentProgress::findOne(['student_id' => $studentId, 'course_id' => $courseId]);
    if (!$progress) {
        $progress             = new StudentProgress();
        $progress->student_id = $studentId;
        $progress->course_id  = $courseId;
    }

    $progress->lessons_completed = $completed;
    $progress->lessons_total     = count($items);
    $progress->percent           = $progress->lessons_total > 0
        ? (int) round($completed / $progress->lessons_total * 100)
        : 0;
    $progress->last_activity_at  = time();
    $progress->save(false);
}
}
