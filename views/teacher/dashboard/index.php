<?php
/** @var app\models\Group[] $groups */
/** @var app\models\ClassSession[] $nextSessions */
/** @var app\models\HomeworkStudent[] $pendingSubmissions */
/** @var int $totalHomeworks */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Дашборд';
?>

<!-- Компактная строка статистики -->
<div class="flex items-center gap-6 mb-6 flex-wrap">
    <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-base-100"><?= count($groups) ?></span>
        <span class="text-sm text-base-400">групп</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-base-100"><?= $totalHomeworks ?></span>
        <span class="text-sm text-base-400">ДЗ создано</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-2xl font-bold <?= count($pendingSubmissions) > 0 ? 'text-acid-pink' : 'text-base-100' ?>">
            <?= count($pendingSubmissions) ?>
        </span>
        <span class="text-sm text-base-400">сдач ждут проверки</span>
    </div>
    <div class="ml-auto flex gap-2">
        <a href="<?= Url::to(['/teacher/schedule/create']) ?>" class="btn-primary text-sm">+ Занятие</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">

    <!-- Требуют проверки — приоритетный блок -->
    <div class="card">
        <h2 class="text-base font-semibold text-base-100 mb-4">Ждут проверки</h2>
        <?php if (empty($pendingSubmissions)): ?>
            <p class="text-base-400 text-sm">Всё проверено 👍</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($pendingSubmissions as $hs): ?>
                    <a href="<?= Url::to(['/teacher/homework/submissions', 'id' => $hs->homework_id]) ?>"
                       class="flex items-center justify-between p-3 rounded-lg bg-base-900 no-underline hover:bg-base-800 transition-colors duration-150">
                        <div>
                            <p class="text-sm font-medium text-base-100"><?= Html::encode($hs->student->name) ?></p>
                            <p class="text-xs text-base-400"><?= Html::encode($hs->homework->title) ?></p>
                        </div>
                        <span class="text-xs text-base-400">
                            <?= Yii::$app->formatter->asRelativeTime($hs->submitted_at) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ближайшие занятия -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-base-100">Ближайшие занятия</h2>
            <a href="<?= Url::to(['/teacher/schedule/index']) ?>" class="text-xs text-acid-lime no-underline">Всё расписание →</a>
        </div>
        <?php if (empty($nextSessions)): ?>
            <p class="text-base-400 text-sm">Занятий не запланировано.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($nextSessions as $s): ?>
                    <a href="<?= Url::to(['/teacher/schedule/view', 'id' => $s->id]) ?>"
                       class="flex items-center justify-between p-3 rounded-lg bg-base-900 no-underline hover:bg-base-800 transition-colors duration-150">
                        <span class="text-sm text-base-100"><?= Html::encode($s->title) ?></span>
                        <span class="text-xs text-base-400">
                            <?= Yii::$app->formatter->asDatetime($s->scheduled_at, 'php:d.m.Y H:i') ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Группы с быстрым доступом к таймлайну -->
<div class="card mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-base-100">Мои группы</h2>
        <a href="<?= Url::to(['/teacher/group/create']) ?>" class="text-xs text-acid-lime no-underline">+ Группа</a>
    </div>
    <?php if (empty($groups)): ?>
        <p class="text-base-400 text-sm">Групп пока нет.</p>
    <?php else: ?>
        <div class="grid grid-cols-3 gap-3">
            <?php foreach ($groups as $group): ?>
                <a href="<?= Url::to(['/teacher/group/timeline', 'id' => $group->id]) ?>"
                   class="p-3 rounded-lg bg-base-900 no-underline hover:bg-base-800 transition-colors duration-150">
                    <p class="text-sm font-medium text-base-100"><?= Html::encode($group->name) ?></p>
                    <p class="text-xs text-base-400 mt-0.5"><?= $group->getStudentCount() ?> учеников</p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>