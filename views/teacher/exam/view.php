<?php
/** @var app\models\Exam $exam */
/** @var app\models\ExamAttempt[] $attempts */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ExamAttempt;

$this->title = $exam->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/exam/index']) ?>" class="hover:text-base-100 no-underline">Экзамены</a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($exam->title) ?></span>
</div>

<div class="card mb-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-base-100 mb-1"><?= Html::encode($exam->title) ?></h1>
            <div class="flex items-center gap-3 text-sm text-base-400">
                <span><?= count($exam->examTasks) ?> задач</span>
                <span><?= $exam->hasTimer() ? $exam->duration_minutes . ' мин' : 'без таймера' ?></span>
                <?php if ($exam->is_proctored): ?>
                    <span class="badge-violet">Защищённый режим</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Попытки (<?= count($attempts) ?>)</h2>

    <?php if (empty($attempts)): ?>
        <p class="text-base-400 text-sm">Никто ещё не начинал экзамен.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($attempts as $attempt): ?>
                <a href="<?= Url::to(['/teacher/exam/attempt', 'id' => $attempt->id]) ?>"
                   class="flex items-center justify-between p-3 rounded-lg bg-base-900
                          hover:bg-base-800 transition-colors duration-150 no-underline">
                    <div>
                        <p class="text-sm font-medium text-base-100"><?= Html::encode($attempt->student->name) ?></p>
                        <p class="text-xs text-base-400 mt-0.5">
                            <?= ExamAttempt::getLabel($attempt->status) ?>
                            <?php if ($attempt->fullscreen_exits > 0 || $attempt->focus_lost_count > 0): ?>
                                · <span class="text-acid-pink">
                                    ⚠ выходов: <?= $attempt->fullscreen_exits ?>, потерь фокуса: <?= $attempt->focus_lost_count ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($attempt->score_total !== null): ?>
                        <span class="font-bold text-acid-lime">
                            <?= $attempt->score_total ?>/<?= $attempt->score_max ?>
                        </span>
                    <?php else: ?>
                        <span class="badge-gray">В процессе</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>