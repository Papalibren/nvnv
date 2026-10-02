<?php
/** @var app\models\ClassSession $session */
/** @var app\models\BookPage[] $bookPages */
/** @var app\models\HomeworkStudent|null $homeworkStudent */
/** @var app\models\ExamAttempt|null $examAttempt */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ClassSession;

$this->title = $session->title;
$statusLabels = ClassSession::getStatusLabels();
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/student/schedule/index']) ?>" class="hover:text-base-100 no-underline">Мои занятия</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($session->title) ?></span>
</div>

<div class="card mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-base-100"><?= Html::encode($session->title) ?></h1>
            <p class="text-sm text-base-400 mt-1">
                <?= Yii::$app->formatter->asDatetime($session->scheduled_at, 'php:d.m.Y H:i') ?>
                <?php if ($session->duration_minutes): ?> · <?= $session->duration_minutes ?> мин<?php endif; ?>
            </p>
        </div>
        <span class="<?= $session->status === 'completed' ? 'badge-indigo' : ($session->status === 'cancelled' ? 'badge-red' : 'badge-gray') ?>">
            <?= $statusLabels[$session->status] ?>
        </span>
    </div>
</div>

<?php if ($bookPages): ?>
<div class="card mb-6">
    <h2 class="text-base font-semibold text-base-100 mb-3">Теория к занятию</h2>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($bookPages as $page): ?>
            <?php $section = $page->chapter->section ?? null; ?>
            <?php if ($section): ?>
                <a href="/book/<?= $section->slug ?>/<?= $page->slug ?>" target="_blank"
                   class="text-sm text-acid-lime hover:text-acid-violet no-underline">
                    📖 <?= Html::encode($page->title) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($session->homework_id): ?>
<div class="card mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-base-100 mb-1">Домашнее задание</h2>
            <p class="text-sm text-base-400"><?= Html::encode($session->homework->title ?? '') ?></p>
        </div>

        <?php if ($session->status !== 'completed'): ?>
            <span class="badge-gray">Откроется после занятия</span>
        <?php elseif ($homeworkStudent): ?>
            <a href="<?= Url::to(['/student/homework/view', 'id' => $homeworkStudent->id]) ?>"
               class="<?= $homeworkStudent->isSubmitted() ? 'btn-secondary' : 'btn-primary' ?> text-sm">
                <?= $homeworkStudent->isSubmitted() ? 'Сдано ✓' : 'Решить' ?>
            </a>
        <?php else: ?>
            <span class="badge-gray">Пока недоступно</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($session->exam_id): ?>
<div class="card mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-base-100 mb-1">Экзамен</h2>
            <p class="text-sm text-base-400"><?= Html::encode($session->exam->title ?? '') ?></p>
        </div>

        <?php if ($session->status !== 'completed'): ?>
            <span class="badge-gray">Откроется после занятия</span>
        <?php elseif ($examAttempt && $examAttempt->isFinished()): ?>
            <a href="<?= Url::to(['/student/exam/result', 'id' => $examAttempt->id]) ?>" class="btn-secondary text-sm">
                Результат: <?= $examAttempt->score_total ?>/<?= $examAttempt->score_max ?>
            </a>
        <?php else: ?>
            <a href="<?= Url::to(['/student/exam/view', 'id' => $session->exam_id]) ?>" class="btn-primary text-sm">
                <?= $examAttempt ? 'Продолжить' : 'Начать экзамен' ?>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($session->notes): ?>
<div class="card">
    <h2 class="text-sm font-semibold text-base-100 mb-2">Заметки учителя</h2>
    <p class="text-sm text-base-100"><?= nl2br(Html::encode($session->notes)) ?></p>
</div>
<?php endif; ?>