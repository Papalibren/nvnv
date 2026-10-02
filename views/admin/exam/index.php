<?php
/** @var app\models\Exam[] $exams */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Публичные экзамены';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Публичные экзамены</h1>
        <p class="text-sm text-base-400 mt-0.5">Доступны самостоятельным ученикам и дают баллы в рейтинг</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/admin/exam/all']) ?>" class="btn-secondary">Все экзамены</a>
        <a href="<?= Url::to(['/admin/exam/create']) ?>" class="btn-primary">+ Новый</a>
    </div>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($exams)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400">Публичных экзаменов пока нет.</p>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr><th>Название</th><th style="width:100px">Задач</th><th style="width:100px">Попыток</th><th style="width:100px"></th></tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr id="exam-row-<?= $exam->id ?>">
                    <td class="font-medium text-base-100"><?= Html::encode($exam->title) ?></td>
                    <td class="text-sm text-base-400"><?= count($exam->examTasks) ?></td>
                    <td class="text-sm text-base-400"><?= count($exam->attempts) ?></td>
                    <td>
                        <?php if (empty($exam->attempts)): ?>
                            <button class="btn-ghost text-xs text-acid-pink"
                                    hx-delete="<?= Url::to(['/admin/exam/delete', 'id' => $exam->id]) ?>"
                                    hx-target="#exam-row-<?= $exam->id ?>"
                                    hx-swap="outerHTML swap:300ms"
                                    hx-confirm="Удалить?">Удалить</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>