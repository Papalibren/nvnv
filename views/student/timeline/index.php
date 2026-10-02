<?php
/** @var array $items */
/** @var string|null $shareToken */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'История обучения';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">История обучения</h1>
</div>

<div class="card mb-6" style="border: 1px solid rgba(79,70,229,0.2); background: rgba(79,70,229,0.03);">
    <p class="text-sm text-base-100 font-medium mb-1">Ссылка для родителя</p>
    <p class="text-xs text-base-400 mb-3">Родитель сможет посмотреть эту историю без входа в систему.</p>
    <?php if ($shareToken): ?>
        <div class="flex items-center gap-2 p-2 rounded-lg bg-white">
            <input type="text" readonly value="<?= Html::encode(Yii::$app->params['siteUrl'] . '/parent/timeline/' . $shareToken) ?>"
                   id="parent-link" class="flex-1 text-xs font-mono outline-none bg-transparent">
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('parent-link').value); this.textContent='Скопировано'"
                    class="btn-secondary text-xs py-1 px-2">Копировать</button>
        </div>
    <?php else: ?>
        <a href="<?= Url::to(['/student/timeline/generate-share-link']) ?>" class="btn-primary text-sm">
            Создать ссылку
        </a>
    <?php endif; ?>
</div>

<?= $this->render('@app/views/timeline/_items', ['items' => $items]) ?>