<?php
/** @var app\models\CourseEnrollment $enrollment */
/** @var app\models\CourseScheduleItem[] $items */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\HomeworkStudent;
use app\models\ExamAttempt;
use app\models\LessonTheoryLink;
use app\models\BookPage;

$this->title = 'Мой курс';
$studentId = Yii::$app->user->id;
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100"><?= Html::encode($enrollment->course->title ?? '') ?></h1>
        <p class="text-sm text-base-400 mt-0.5">
            Срок: <?= $enrollment->duration_months ?> мес. до
            <?= Yii::$app->formatter->asDate($enrollment->target_end_at, 'php:d.m.Y') ?>
        </p>
    </div>
    <?php if (!$enrollment->duration_changed): ?>
        <a href="<?= Url::to(['/student/course/change-duration', 'enrollmentId' => $enrollment->id]) ?>" class="btn-secondary text-sm">
            Изменить срок
        </a>
    <?php endif; ?>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="space-y-3">
    <?php foreach ($items as $item): ?>
        <?php
        $lesson       = $item->courseLesson->lesson ?? null;
        $unlocked     = $item->isUnlocked();
        $completed    = $item->completed_at !== null;
        $isCheckpoint = $item->courseLesson->isCheckpoint();

        // Теория темы
        $theoryPageIds = LessonTheoryLink::find()
            ->where(['lesson_id' => $lesson->id ?? 0, 'content_type' => 'book_page'])
            ->select('content_id')->column();
        $theoryPages = $theoryPageIds
            ? BookPage::find()->where(['id' => $theoryPageIds])->with('chapter.section')->all()
            : [];

        // Статус ДЗ
        $hs = $item->homework_id
            ? HomeworkStudent::findOne(['homework_id' => $item->homework_id, 'student_id' => $studentId])
            : null;

        // Статус экзамена-чекпоинта
        $examAttempt = $isCheckpoint
            ? ExamAttempt::findOne(['exam_id' => $item->courseLesson->checkpoint_exam_id, 'student_id' => $studentId])
            : null;
        ?>

        <div class="card <?= !$unlocked ? 'opacity-50' : '' ?>"
             style="<?= $completed ? 'border-left: 3px solid #4F46E5;' : '' ?>">

            <div class="flex items-center gap-3 mb-3">
                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                             <?= $completed ? 'bg-acid-lime text-white' : ($unlocked ? 'bg-acid-cyan/20 text-acid-cyan' : 'bg-base-700 text-base-400') ?>">
                    <?= $completed ? '✓' : ($unlocked ? '●' : '🔒') ?>
                </span>
                <div class="flex-1">
                    <p class="font-medium text-base-100">
                        <?= Html::encode($lesson->title ?? '') ?>
                        <?php if ($isCheckpoint): ?>
                            <span class="badge-violet ml-1">Контрольная точка</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-xs text-base-400 mt-0.5">
                        <?= $unlocked
                            ? ($completed ? 'Пройдено' : 'Доступно')
                            : 'Откроется ' . Yii::$app->formatter->asDate($item->unlock_at, 'php:d.m.Y') ?>
                    </p>
                </div>
            </div>

            <!-- Теория — всегда видна для чтения, даже если тема ещё не открыта -->
            <?php if ($theoryPages && !($lesson && $lesson->isInfo())): ?>
                <div class="mb-3 pl-11">
                    <p class="text-xs text-base-400 mb-1.5">Теория:</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($theoryPages as $page): ?>
                            <?php $section = $page->chapter->section ?? null; ?>
                            <?php if ($section): ?>
                                <a href="/book/<?= $section->slug ?>/<?= $page->slug ?>"
                                   class="text-xs text-acid-lime hover:text-acid-violet no-underline"
                                   target="_blank">
                                    📖 <?= Html::encode($page->title) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Практика / экзамен — только если открыто -->
            <?php if ($unlocked): ?>
                <div class="pl-11">

                    <?php if ($lesson && $lesson->isInfo()): ?>
                        <!-- Информационная тема — текст + кнопка "прочитано" -->
                        <?php $isRead = $lesson->isReadBy($studentId); ?>
                        <div class="prose-task text-sm mb-3 p-3 rounded-lg bg-base-900">
                            <?= \app\helpers\ContentRenderer::render($lesson->info_content ?? '') ?>
                        </div>
                        <?php if ($isRead): ?>
                            <span class="btn-secondary text-xs py-1.5 px-3 inline-block">Прочитано ✓</span>
                        <?php else: ?>
                            <a href="<?= Url::to(['/student/course/mark-read', 'lessonId' => $lesson->id]) ?>"
                            class="btn-primary text-xs py-1.5 px-3">
                                Отметить как прочитанное
                            </a>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Практическая тема — как раньше -->
                        <div class="flex flex-wrap gap-2">
                            <?php if ($hs): ?>
                                <a href="<?= Url::to(['/student/homework/view', 'id' => $hs->id]) ?>"
                                class="<?= $hs->isSubmitted() ? 'btn-secondary' : 'btn-primary' ?> text-xs py-1.5 px-3">
                                    <?= $hs->isSubmitted() ? 'Практика сдана ✓' : 'Решить практику' ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($isCheckpoint): ?>
                                <?php if ($examAttempt && $examAttempt->isFinished()): ?>
                                    <a href="<?= Url::to(['/student/exam/result', 'id' => $examAttempt->id]) ?>"
                                    class="btn-secondary text-xs py-1.5 px-3">
                                        Результат: <?= $examAttempt->score_total ?>/<?= $examAttempt->score_max ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= Url::to(['/student/exam/view', 'id' => $item->courseLesson->checkpoint_exam_id]) ?>"
                                    class="btn-primary text-xs py-1.5 px-3">
                                        <?= $examAttempt ? 'Продолжить экзамен' : 'Пройти экзамен' ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>