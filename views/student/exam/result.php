<?php
/** @var app\models\ExamAttempt $attempt */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = 'Результат';
$exam = $attempt->exam;
?>

<div class="max-w-2xl mx-auto">

    <div class="card mb-6 text-center">
        <h1 class="text-2xl font-bold text-base-100 mb-4"><?= Html::encode($exam->title) ?></h1>

        <div class="text-5xl font-bold text-acid-lime mb-2">
            <?= $attempt->score_total ?><span class="text-2xl text-base-400">/<?= $attempt->score_max ?></span>
        </div>

        <?php if ($exam->is_proctored): ?>
            <span class="badge-violet">Учтено в рейтинге</span>
        <?php endif; ?>

        <?php if ($attempt->status === 'expired'): ?>
            <p class="text-sm text-acid-pink mt-3">Время истекло, экзамен завершён автоматически</p>
        <?php endif; ?>
    </div>

    <div class="space-y-3">
        <?php foreach ($attempt->answers as $answer): ?>
            <div class="card" style="border-left: 3px solid <?= $answer->is_correct ? '#4F46E5' : '#E11D48' ?>;">
                <div class="flex items-center justify-between mb-2">
                    <span class="badge-indigo">
                        <?= $answer->task->task_number ? 'Задание ' . $answer->task->task_number : 'Без номера' ?>
                    </span>
                    <span class="<?= $answer->is_correct ? 'text-acid-lime' : 'text-acid-pink' ?> font-bold">
                        <?= $answer->is_correct ? '✓' : '✗' ?>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-2 rounded bg-base-900">
                        <p class="text-xs text-base-400">Ваш ответ</p>
                        <code class="font-mono"><?= Html::encode($answer->student_answer ?: '—') ?></code>
                    </div>
                    <div class="p-2 rounded bg-base-900">
                        <p class="text-xs text-base-400">Правильный</p>
                        <code class="font-mono"><?= Html::encode($answer->correct_answer_snapshot) ?></code>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="<?= Url::to(['/student/exam/index']) ?>" class="btn-secondary">К списку экзаменов</a>
    </div>
</div>