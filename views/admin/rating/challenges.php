<?php
/** @var app\models\PublicChallenge[] $challenges */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Публичные задачи';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Публичные задачи</h1>
    <a href="<?= Url::to(['/admin/rating/create-challenge']) ?>" class="btn-primary">+ Новая</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($challenges)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Пока нет ни одной задачи.</p></div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead><tr><th>Название</th><th>Баллы</th><th>Открытие</th><th>Закрытие</th><th>Ответов</th></tr></thead>
            <tbody>
                <?php foreach ($challenges as $c): ?>
                <tr>
                    <td class="font-medium text-base-100"><?= Html::encode($c->title) ?></td>
                    <td><?= $c->points ?></td>
                    <td class="text-sm text-base-400"><?= Yii::$app->formatter->asDatetime($c->opens_at, 'php:d.m.Y H:i') ?></td>
                    <td class="text-sm text-base-400"><?= Yii::$app->formatter->asDatetime($c->closes_at, 'php:d.m.Y H:i') ?></td>
                    <td><?= $c->getAttempts()->count() ?? 0 ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>