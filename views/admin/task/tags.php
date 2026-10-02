<?php
/** @var yii\web\View $this */
/** @var app\models\TaskTag[] $tags */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Теги задач';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Теги задач</h1>
    <a href="<?= Url::to(['/admin/task/index']) ?>" class="btn-secondary">
        ← К задачам
    </a>
</div>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert-error mb-4">
        <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-6">

    <!-- Форма добавления -->
    <div class="card">
        <h2 class="text-base font-semibold text-base-100 mb-4">Новый тег</h2>
        <form method="post" action="<?= Url::to(['/admin/task/tags']) ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="field-group">
                <label>Название тега</label>
                <input type="text" name="name" class="input" placeholder="Например: Сортировки" required>
            </div>
            <?= Html::submitButton('Добавить', ['class' => 'btn-primary w-full']) ?>
        </form>
    </div>

    <!-- Список тегов -->
    <div class="col-span-2 card">
        <h2 class="text-base font-semibold text-base-100 mb-4">
            Существующие теги (<?= count($tags) ?>)
        </h2>

        <?php if ($tags): ?>
            <div id="tag-list" class="space-y-2">
                <?php foreach ($tags as $tag): ?>
                    <?= $this->render('_tag_row', ['tag' => $tag]) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-base-400 text-sm">Тегов пока нет.</p>
        <?php endif; ?>
    </div>

</div>