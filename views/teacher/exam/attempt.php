<?php
/** @var app\models\ExamAttempt $attempt */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = 'Результат: ' . $attempt->student->name;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/exam/view', 'id' => $attempt->exam_id]) ?>"
       class="hover:text-base-100 no-underline"><?= Html::encode($attempt->exam->title) ?></a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($attempt->student->name) ?></span>
</div>

<div class="card mb-6">
    <div class="grid grid-cols-3 gap-4 text-center">
        <div>
            <p class="text-2xl font-bold text-acid-lime"><?= $attempt->score_total ?? '—' ?></p>
            <p class="text-xs text-base-400">из <?= $attempt->score_max ?? '—' ?></p>
        </div>
        <div>
            <p class="text-2xl font-bold text-base-100"><?= $attempt->fullscreen_exits ?></p>
            <p class="text-xs text-base-400">выходов из полноэкранного</p>
        </div>
        <div>
            <p class="text-2xl font-bold text-base-100"><?= $attempt->focus_lost_count ?></p>
            <p class="text-xs text-base-400">потерь фокуса окна</p>
        </div>
    </div>
</div>

<div class="space-y-3">
    <?php foreach ($attempt->answers as $answer): ?>
        <div class="card">
            <div class="flex items-center justify-between mb-2">
                <span class="badge-indigo">
                    <?= $answer->task->task_number ? 'Задание ' . $answer->task->task_number : 'Без номера' ?>
                </span>
                <span class="<?= $answer->is_correct ? 'text-acid-lime' : 'text-acid-pink' ?> font-bold">
                    <?= $answer->is_correct ? '✓' : '✗' ?> <?= $answer->points_earned ?> б.
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm mt-2">
                <div class="p-2 rounded bg-base-900">
                    <p class="text-xs text-base-400">Ответ ученика</p>
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