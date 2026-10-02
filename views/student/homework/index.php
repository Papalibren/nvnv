<?php
/** @var app\models\HomeworkStudent[] $active */
/** @var app\models\HomeworkStudent[] $done */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Домашние задания';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-base-100">Домашние задания</h1>
</div>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert-error mb-4"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
<?php endif; ?>

<!-- Активные ДЗ -->
<div class="card mb-6">
    <h2 class="text-base font-semibold text-base-100 mb-4">
        Активные (<?= count($active) ?>)
    </h2>

    <?php if (empty($active)): ?>
        <p class="text-base-400 text-sm">Активных заданий нет.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($active as $hs): ?>
                <?php $hw = $hs->homework; ?>
                <a href="<?= Url::to(['/student/homework/view', 'id' => $hs->id]) ?>"
                   class="flex items-center justify-between p-4 rounded-xl bg-base-900
                          hover:bg-base-800 transition-colors duration-150 no-underline">
                    <div>
                        <p class="font-semibold text-base-100"><?= Html::encode($hw->title) ?></p>
                        <p class="text-xs text-base-400 mt-0.5">
                            <?= count($hw->homeworkTasks) ?> задач
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <?php if ($hw->deadline_at): ?>
                            <p class="text-xs <?= $hw->isOverdue() ? 'text-acid-pink font-semibold' : 'text-base-400' ?>">
                                <?= $hw->isOverdue() ? 'Просрочено' : 'До' ?>
                                <?= Yii::$app->formatter->asDate($hw->deadline_at, 'php:d.m.Y') ?>
                            </p>
                        <?php endif; ?>
                        <span class="badge-<?= $hs->status === 'in_progress' ? 'cyan' : 'gray' ?> mt-1">
                            <?= \app\models\HomeworkStudent::getLabel($hs->status) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Сданные ДЗ -->
<?php if (!empty($done)): ?>
<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Сданные</h2>
    <div class="space-y-2">
        <?php foreach ($done as $hs): ?>
            <a href="<?= Url::to(['/student/homework/result', 'id' => $hs->id]) ?>"
               class="flex items-center justify-between p-3 rounded-lg bg-base-900
                      hover:bg-base-800 transition-colors duration-150 no-underline">
                <span class="text-sm text-base-100"><?= Html::encode($hs->homework->title) ?></span>
                <span class="badge-indigo">Сдано</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>