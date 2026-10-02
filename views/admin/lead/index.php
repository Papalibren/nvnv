<?php
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Landing[] $landings */
/** @var int $newCount */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Заявки';
$currentLandingId = Yii::$app->request->get('landing_id');
$onlyNew          = Yii::$app->request->get('new');
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Заявки</h1>
        <p class="text-sm text-base-400 mt-0.5">
            Всего: <?= $dataProvider->totalCount ?>,
            новых: <span class="text-acid-pink font-semibold"><?= $newCount ?></span>
        </p>
    </div>
</div>

<!-- Фильтры -->
<div class="flex gap-2 mb-6 flex-wrap">
    <a href="<?= Url::to(['/admin/lead/index']) ?>"
       class="<?= !$currentLandingId && !$onlyNew ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
        Все
    </a>
    <a href="<?= Url::to(['/admin/lead/index', 'new' => 1]) ?>"
       class="<?= $onlyNew ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
        Только новые
    </a>
    <?php foreach ($landings as $landing): ?>
        <a href="<?= Url::to(['/admin/lead/index', 'landing_id' => $landing->id]) ?>"
           class="<?= $currentLandingId == $landing->id ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
            <?= Html::encode($landing->title) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card p-0 overflow-hidden">
    <table class="table-base">
        <thead>
            <tr>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Лендинг</th>
                <th>UTM</th>
                <th style="width:110px">Дата</th>
                <th style="width:100px">Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $lead): ?>
            <tr>
                <td class="font-medium text-base-100"><?= Html::encode($lead->name) ?></td>
                <td class="font-mono text-sm"><?= Html::encode($lead->phone) ?></td>
                <td class="text-sm text-base-400"><?= Html::encode($lead->email ?: '—') ?></td>
                <td class="text-sm">
                    <?= $lead->landing ? Html::encode($lead->landing->title) : '—' ?>
                </td>
                <td class="text-xs text-base-400">
                    <?php if ($lead->utm_source): ?>
                        <?= Html::encode($lead->utm_source) ?>
                        <?= $lead->utm_campaign ? ' / ' . Html::encode($lead->utm_campaign) : '' ?>
                    <?php else: ?>
                        прямой заход
                    <?php endif; ?>
                </td>
                <td class="text-xs text-base-400">
                    <?= Yii::$app->formatter->asDate($lead->created_at, 'php:d.m.Y') ?>
                </td>
                <td>
                    <span id="lead-processed-<?= $lead->id ?>">
                        <?= $this->render('_processed_badge', ['lead' => $lead]) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($dataProvider->pagination->pageCount > 1): ?>
    <div class="mt-4 flex justify-center">
        <?= \yii\widgets\LinkPager::widget([
            'pagination'  => $dataProvider->pagination,
            'options'     => ['class' => 'flex gap-1'],
            'linkOptions' => ['class' => 'btn-secondary text-sm py-1.5 px-3'],
            'activePageCssClass' => 'btn-primary text-sm py-1.5 px-3',
        ]) ?>
    </div>
<?php endif; ?>