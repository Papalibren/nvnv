<?php
/** @var app\models\Exam[] $exams */
/** @var int $studentId */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Экзамены';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-base-100">Пробные экзамены</h1>
</div>

<?php if (empty($exams)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400">Пока нет доступных экзаменов.</p>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($exams as $exam): ?>
            <?php $attempt = $exam->getStudentAttempt($studentId); ?>
            <div class="card flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-base-100"><?= Html::encode($exam->title) ?></p>
                        <?php if ($exam->is_proctored): ?>
                            <span class="badge-violet">Даёт рейтинг</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-base-400">
                        <?= count($exam->examTasks) ?> задач ·
                        <?= $exam->hasTimer() ? $exam->duration_minutes . ' минут' : 'без таймера' ?>
                    </p>
                </div>

                <?php if (!$attempt): ?>
                    <a href="<?= Url::to(['/student/exam/view', 'id' => $exam->id]) ?>" class="btn-primary">
                        Начать
                    </a>
                <?php elseif ($attempt->isInProgress()): ?>
                    <a href="<?= Url::to(['/student/exam/view', 'id' => $exam->id]) ?>" class="btn-secondary">
                        Продолжить
                    </a>
                <?php else: ?>
                    <a href="<?= Url::to(['/student/exam/result', 'id' => $attempt->id]) ?>" class="btn-ghost">
                        Результат: <?= $attempt->score_total ?>/<?= $attempt->score_max ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>