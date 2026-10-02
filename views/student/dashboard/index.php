<?php
/** @var yii\web\View $this */
/** @var app\models\HomeworkStudent[] $activeHomework */
/** @var int $totalPoints */
/** @var app\models\User|null $teacher */
/** @var app\models\ClassSession|null $nextSession */
/** @var array $heatmapWeeks */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Дашборд';
?>

<!-- Компактная строка статистики -->
<div class="flex items-center gap-6 mb-6 flex-wrap">
    <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-acid-lime"><?= $totalPoints ?></span>
        <span class="text-sm text-base-400">баллов</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-base-100"><?= count($activeHomework) ?></span>
        <span class="text-sm text-base-400">активных ДЗ</span>
    </div>
    <?php if ($teacher): ?>
        <span class="ml-auto text-sm text-base-400">
            Учитель: <span class="text-base-100 font-medium"><?= Html::encode($teacher->name) ?></span>
        </span>
    <?php endif; ?>
</div>

<!-- Ближайшее занятие -->
<?php if ($nextSession): ?>
<a href="<?= Url::to(['/student/schedule/view', 'id' => $nextSession->id]) ?>"
   class="card mb-6 flex items-center justify-between no-underline hover:border-acid-lime/40 transition-colors duration-150"
   style="border: 1px solid rgba(79,70,229,0.25); background: rgba(79,70,229,0.03);">
    <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-full bg-acid-cyan/15 text-acid-cyan flex items-center justify-center shrink-0">
            📅
        </span>
        <div>
            <p class="text-xs text-base-400">Ближайшее занятие</p>
            <p class="font-semibold text-base-100"><?= Html::encode($nextSession->title) ?></p>
            <p class="text-xs text-base-400 mt-0.5">
                <?= Yii::$app->formatter->asDatetime($nextSession->scheduled_at, 'php:d.m.Y H:i') ?>
            </p>
        </div>
    </div>
    <span class="badge-indigo shrink-0">Подробнее →</span>
</a>
<?php endif; ?>

<!-- Тепловая карта активности -->
<div class="card mb-6">
    <h2 class="text-base font-semibold text-base-100 mb-4">Активность</h2>
    <?= $this->render('@app/views/shared/_activity_heatmap', ['weeks' => $heatmapWeeks]) ?>
</div>

<!-- Активные ДЗ -->
<?php if ($teacher): ?>

<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Домашние задания</h2>

    <?php if (empty($activeHomework)): ?>
        <p class="text-base-400 text-sm">Активных заданий пока нет.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($activeHomework as $hs): ?>
                <?php $hw = $hs->homework; ?>
                <a href="<?= Url::to(['/student/homework/view', 'id' => $hs->id]) ?>"
                   class="flex items-center justify-between p-4 rounded-lg bg-base-900 no-underline
                          hover:bg-base-800 transition-colors duration-150">
                    <div>
                        <p class="font-medium text-base-100"><?= Html::encode($hw->title) ?></p>
                        <?php if ($hw->deadline_at): ?>
                            <p class="text-xs <?= $hw->isOverdue() ? 'text-acid-pink' : 'text-base-400' ?> mt-0.5">
                                Дедлайн: <?= Yii::$app->formatter->asDatetime($hw->deadline_at, 'php:d.m.Y H:i') ?>
                                <?= $hw->isOverdue() ? '(просрочено)' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <span class="badge-gray shrink-0">
                        <?= \app\models\HomeworkStudent::getLabel($hs->status) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php else: ?>

<div class="card text-center py-12">
    <p class="text-base-400">Свяжитесь с администратором, чтобы начать занятия.</p>
</div>

<?php endif; ?>