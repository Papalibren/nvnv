<?php
/** @var app\models\Homework[] $homeworks */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Домашние задания';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Домашние задания</h1>
    <a href="<?= Url::to(['/teacher/homework/create']) ?>" class="btn-primary">+ Новое ДЗ</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($homeworks)): ?>
    <div class="card text-center py-12">
        <p class="text-base-400 mb-4">ДЗ пока нет.</p>
        <a href="<?= Url::to(['/teacher/homework/create']) ?>" class="btn-primary">Создать первое ДЗ</a>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Название</th>
                    <th style="width:150px">Группа</th>
                    <th style="width:130px">Дедлайн</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homeworks as $hw): ?>
                <tr>
                    <td class="font-medium text-base-100"><?= Html::encode($hw->title) ?></td>
                    <td class="text-base-400 text-sm">
                        <?= $hw->group ? Html::encode($hw->group->name) : '—' ?>
                    </td>
                    <td>
                        <?php if ($hw->deadline_at): ?>
                            <span class="text-sm <?= $hw->isOverdue() ? 'text-acid-pink' : 'text-base-400' ?>">
                                <?= Yii::$app->formatter->asDate($hw->deadline_at, 'php:d.m.Y') ?>
                            </span>
                        <?php else: ?>
                            <span class="text-base-400 text-sm">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/teacher/homework/view', 'id' => $hw->id]) ?>"
                           class="btn-ghost text-xs">Открыть</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>