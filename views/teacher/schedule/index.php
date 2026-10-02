<?php
/** @var app\models\ClassSession[] $upcoming */
/** @var app\models\ClassSession[] $past */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ClassSession;

$this->title = 'Расписание';
$statusLabels = ClassSession::getStatusLabels();
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Расписание</h1>
    <a href="<?= Url::to(['/teacher/schedule/create']) ?>" class="btn-primary">+ Новое занятие</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="card mb-6">
    <h2 class="text-base font-semibold text-base-100 mb-4">Ближайшие занятия</h2>
    <?php if (empty($upcoming)): ?>
        <p class="text-base-400 text-sm">Занятий не запланировано.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($upcoming as $s): ?>
                <?php $isOverdue = $s->isPast(); ?>
                <a href="<?= Url::to(['/teacher/schedule/view', 'id' => $s->id]) ?>"
                   class="flex items-center justify-between p-3 rounded-lg no-underline hover:bg-base-800 transition-colors duration-150
                          <?= $isOverdue ? 'bg-acid-pink/5 border border-acid-pink/20' : 'bg-base-900' ?>">
                    <div>
                        <p class="font-medium text-base-100"><?= Html::encode($s->title) ?></p>
                        <p class="text-xs text-base-400 mt-0.5">
                            <?= $s->student ? Html::encode($s->student->name) : Html::encode($s->group->name ?? '') ?>
                            · <?= Yii::$app->formatter->asDatetime($s->scheduled_at, 'php:d.m.Y H:i') ?>
                        </p>
                    </div>
                    <span class="<?= $isOverdue ? 'badge-red' : 'badge-gray' ?>">
                        <?= $isOverdue ? 'Просрочено' : $statusLabels[$s->status] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">История</h2>
    <?php if (empty($past)): ?>
        <p class="text-base-400 text-sm">Пока нет проведённых занятий.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($past as $s): ?>
                <a href="<?= Url::to(['/teacher/schedule/view', 'id' => $s->id]) ?>"
                   class="flex items-center justify-between p-3 rounded-lg bg-base-900 no-underline hover:bg-base-800 transition-colors duration-150">
                    <div>
                        <p class="text-sm text-base-100"><?= Html::encode($s->title) ?></p>
                        <p class="text-xs text-base-400 mt-0.5">
                            <?= Yii::$app->formatter->asDatetime($s->scheduled_at, 'php:d.m.Y H:i') ?>
                        </p>
                    </div>
                    <span class="<?= $s->status === 'completed' ? 'badge-indigo' : 'badge-gray' ?>">
                        <?= $statusLabels[$s->status] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>