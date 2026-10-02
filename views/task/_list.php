<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use app\widgets\TaskWidget;

$tasks = $dataProvider->getModels();
?>

<?php if (empty($tasks)): ?>
    <div class="card text-center py-12">
        <p class="text-base-400">По вашему запросу задачи не найдены.</p>
    </div>
<?php else: ?>

<?php foreach ($tasks as $task): ?>
    <?= TaskWidget::widget(['task' => $task, 'mode' => 'public', 'showOpenLink' => true]) ?>
<?php endforeach; ?>

    <?php if ($dataProvider->pagination->pageCount > 1): ?>
        <div class="mt-6 flex justify-center">
            <?= \yii\widgets\LinkPager::widget([
                'pagination'           => $dataProvider->pagination,
                'options'              => ['class' => 'flex gap-1'],
                'linkOptions'          => ['class' => 'btn-secondary text-sm py-1.5 px-3'],
                'activePageCssClass'   => 'btn-primary text-sm py-1.5 px-3',
                'disabledPageCssClass' => 'opacity-40 cursor-not-allowed',
            ]) ?>
        </div>
    <?php endif; ?>

<?php endif; ?>