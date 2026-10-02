<?php

/** @var app\models\HomeworkStudent $hs */
/** @var app\models\HomeworkAnswer[] $answers */
/** @var int $totalPoints */
/** @var int $correctCount */

use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = 'Результат';
$hw = $hs->homework;
$totalTasks = count($hw->homeworkTasks);
?>

<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="<?= Url::to(['/student/homework/index']) ?>"
            class="hover:text-base-100 no-underline">ДЗ</a>
        <span>/</span>
        <span class="text-base-100"><?= Html::encode($hw->title) ?></span>
    </div>

    <!-- Итог -->
    <div class="card mb-6 text-center">
        <h1 class="text-2xl font-bold text-base-100 mb-6">Результат</h1>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-base-900">
                <p class="text-3xl font-bold text-acid-lime"><?= $totalPoints ?></p>
                <p class="text-xs text-base-400 mt-1">Баллов получено</p>
            </div>
            <div class="p-4 rounded-xl bg-base-900">
                <p class="text-3xl font-bold text-base-100">
                    <?= $correctCount ?>/<?= $totalTasks ?>
                </p>
                <p class="text-xs text-base-400 mt-1">Верных ответов</p>
            </div>
            <div class="p-4 rounded-xl bg-base-900">
                <p class="text-3xl font-bold text-base-100">
                    <?= $totalTasks > 0 ? round($correctCount / $totalTasks * 100) : 0 ?>%
                </p>
                <p class="text-xs text-base-400 mt-1">Точность</p>
            </div>
        </div>
    </div>

    <!-- Разбор по задачам -->
    <div class="space-y-4">
        <?php foreach ($hw->homeworkTasks as $index => $ht): ?>
            <?php
            $answer    = $answers[$ht->id] ?? null;
            $isCorrect = $answer && $answer->is_correct;
            $canRetry  = $answer && !$isCorrect && $answer->attempt_number < 2;

            // Проверяем не было ли уже второй попытки
            $hasSecondAttempt = false;
            foreach ($hs->answers as $a) {
                if ($a->homework_task_id === $ht->id && $a->attempt_number === 2) {
                    $hasSecondAttempt = true;
                    break;
                }
            }
            $canRetry = $canRetry && !$hasSecondAttempt;
            ?>

            <div class="card" style="border-left: 3px solid <?= $isCorrect ? '#4F46E5' : '#E11D48' ?>;">

                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="badge-indigo">Задача <?= $index + 1 ?></span>
                        <span class="text-xl <?= $isCorrect ? 'text-acid-lime' : 'text-acid-pink' ?>">
                            <?= $isCorrect ? '✓' : '✗' ?>
                        </span>
                    </div>
                    <span class="text-sm font-semibold <?= $isCorrect ? 'text-acid-lime' : 'text-acid-pink' ?>">
                        +<?= $answer ? $answer->points_earned : 0 ?> б.
                    </span>
                </div>

                <!-- Условие задачи -->
                <div class="prose-task text-sm text-base-400 mb-3 line-clamp-2">
                    <?= ContentRenderer::render(mb_substr(strip_tags($ht->task->content), 0, 150)) ?>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-3 rounded-lg bg-base-900">
                        <p class="text-xs text-base-400 mb-1">Ваш ответ</p>
                        <p class="font-mono text-base-100">
                            <?= $answer && $answer->answer_text ? Html::encode($answer->answer_text) : '—' ?>
                        </p>
                        <?php if ($answer && $answer->file_path): ?>
                            <a href="/files/<?= $answer->file_path ?>"
                                class="inline-flex items-center gap-1.5 text-xs text-acid-lime mt-2 no-underline"
                                target="_blank">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32" />
                                </svg>
                                Прикреплённый файл
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($isCorrect || $hasSecondAttempt): ?>
                        <div class="p-3 rounded-lg <?= $isCorrect ? 'bg-acid-lime/10' : 'bg-acid-pink/10' ?>">
                            <p class="text-xs text-base-400 mb-1">Правильный ответ</p>
                            <p class="font-mono font-semibold text-base-100">
                                <?= Html::encode($answer->correct_answer_snapshot ?? $ht->task->answer) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="p-3 rounded-lg bg-base-900">
                            <p class="text-xs text-base-400 mb-1">Правильный ответ</p>
                            <p class="text-xs text-base-400 italic">Скрыт — есть ещё одна попытка</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Вторая попытка -->
                <?php if ($canRetry): ?>
                    <div class="mt-3 pt-3" style="border-top: 1px solid #E2E8F0;">
                        <p class="text-xs text-base-400 mb-2">
                            Одна попытка исправить ответ (×0.8 баллов)
                        </p>
                        <form method="post"
                            action="<?= Url::to(['/student/homework/retry', 'id' => $hs->id]) ?>"
                            class="flex gap-2">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <?= Html::hiddenInput('homework_task_id', $ht->id) ?>
                            <input type="text" name="answer" placeholder="Исправленный ответ"
                                class="input font-mono text-sm flex-1" required>
                            <?= Html::submitButton('Исправить', ['class' => 'btn-secondary text-sm py-2 px-4 shrink-0']) ?>
                        </form>
                    </div>
                <?php elseif ($hasSecondAttempt): ?>
                    <?php
                    $secondAnswer = null;
                    foreach ($hs->answers as $a) {
                        if ($a->homework_task_id === $ht->id && $a->attempt_number === 2) {
                            $secondAnswer = $a;
                        }
                    }
                    ?>
                    <?php if ($secondAnswer): ?>
                        <div class="mt-3 pt-3 text-xs text-base-400" style="border-top: 1px solid #E2E8F0;">
                            Вторая попытка: <span class="font-mono text-base-100"><?= Html::encode($secondAnswer->answer_text) ?></span>
                            — <?= $secondAnswer->is_correct ? '<span class="text-acid-lime">верно</span>' : '<span class="text-acid-pink">неверно</span>' ?>
                            (+<?= $secondAnswer->points_earned ?> б.)
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

<!-- Разбор — только если решено, или если больше нет попыток -->
<?php if ($ht->task->solution_content && $ht->task->solution_is_public && ($isCorrect || $hasSecondAttempt)): ?>                    <details class="mt-3" style="border-top: 1px solid #E2E8F0;">
                        <summary class="pt-3 cursor-pointer text-xs text-base-400 hover:text-base-100 transition-colors duration-150 select-none">
                            Разбор решения
                        </summary>
                        <div class="pt-3 text-sm prose-task">
                            <?= ContentRenderer::render($ht->task->solution_content) ?>
                        </div>
                    </details>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-8 flex justify-center gap-4">
        <a href="<?= Url::to(['/student/homework/index']) ?>" class="btn-secondary">
            К списку ДЗ
        </a>
        <a href="<?= Url::to(['/student/dashboard/index']) ?>" class="btn-primary">
            На дашборд
        </a>
    </div>

</div>