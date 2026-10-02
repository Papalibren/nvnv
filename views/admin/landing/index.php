<?php
/** @var app\models\Landing[] $landings */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Лендинги';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Лендинги</h1>
    <a href="<?= Url::to(['/admin/landing/create']) ?>" class="btn-primary">+ Новый лендинг</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($landings)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400 mb-4">Лендингов пока нет.</p>
        <a href="<?= Url::to(['/admin/landing/create']) ?>" class="btn-primary">Создать первый</a>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Название</th>
                    <th style="width:200px">URL</th>
                    <th style="width:100px">Заявок</th>
                    <th style="width:120px">Статус</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($landings as $landing): ?>
                <tr id="landing-row-<?= $landing->id ?>">
                    <td class="font-medium text-base-100">
                        <?= Html::encode($landing->title) ?>
                    </td>
                    <td>
                        <a href="/<?= $landing->slug ?>" target="_blank"
                           class="text-xs text-acid-cyan font-mono no-underline hover:text-acid-lime">
                            /<?= $landing->slug ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/admin/lead/index', 'landing_id' => $landing->id]) ?>"
                           class="badge-indigo no-underline">
                            <?= $landing->getLeads()->count() ?>
                        </a>
                    </td>
                    <td><?= $this->render('_status_badge', ['landing' => $landing]) ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="<?= Url::to(['/admin/landing/update', 'id' => $landing->id]) ?>"
                               class="btn-ghost text-xs">Изменить</a>
                            <button class="btn-ghost text-xs text-acid-pink"
                                    hx-delete="<?= Url::to(['/admin/landing/delete', 'id' => $landing->id]) ?>"
                                    hx-target="#landing-row-<?= $landing->id ?>"
                                    hx-swap="outerHTML swap:300ms"
                                    hx-confirm="Удалить лендинг?">
                                Удалить
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>