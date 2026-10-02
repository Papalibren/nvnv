<?php
/** @var app\models\SlideDeck $deck */
/** @var app\models\Slide[] $slides */
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\KatexAsset;
use app\assets\PrismAsset;

KatexAsset::register($this);
PrismAsset::register($this);
$this->title = 'Слайды: ' . $deck->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/lesson/view', 'id' => $deck->lesson_id]) ?>" class="hover:text-base-100 no-underline">
        <?= Html::encode($deck->lesson->title ?? '') ?>
    </a>
    <span>/</span><span class="text-base-100"><?= Html::encode($deck->title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <form method="post" class="flex items-center gap-3">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <input type="text" name="title" value="<?= Html::encode($deck->title) ?>" class="input" style="width:280px;">
        <select name="status" class="input" style="width:140px;">
            <option value="draft" <?= $deck->status === 'draft' ? 'selected' : '' ?>>Черновик</option>
            <option value="ready" <?= $deck->status === 'ready' ? 'selected' : '' ?>>Готово</option>
        </select>
        <?= Html::submitButton('Сохранить', ['class' => 'btn-secondary']) ?>
    </form>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/teacher/slide/present', 'id' => $deck->id]) ?>" target="_blank" class="btn-primary">Открыть презентацию</a>
        <a href="<?= Url::to(['/admin/slide/add-slide', 'id' => $deck->id]) ?>" class="btn-secondary">+ Слайд</a>
    </div>
</div>

<div class="space-y-4">
    <?php foreach ($slides as $i => $slide): ?>
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <span class="badge-indigo">Слайд <?= $i + 1 ?></span>
                <div class="flex items-center gap-2">
                    <a href="<?= Url::to(['/admin/slide/move-slide', 'id' => $slide->id, 'direction' => 'up']) ?>" class="btn-ghost text-xs">↑</a>
                    <a href="<?= Url::to(['/admin/slide/move-slide', 'id' => $slide->id, 'direction' => 'down']) ?>" class="btn-ghost text-xs">↓</a>
                    <button class="btn-ghost text-xs text-acid-pink"
                            hx-delete="<?= Url::to(['/admin/slide/delete-slide', 'id' => $slide->id]) ?>"
                            hx-target="closest div.card" hx-swap="outerHTML swap:300ms"
                            hx-confirm="Удалить слайд?">Удалить</button>
                </div>
            </div>

            <form method="post" action="<?= Url::to(['/admin/slide/update-slide', 'id' => $slide->id]) ?>" class="grid grid-cols-2 gap-4">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="mb-0">Содержимое слайда (Markdown + KaTeX + код)</label>
                        <button type="button" onclick="openMediaModal('slide-content-<?= $slide->id ?>')"
                                class="btn-secondary text-xs py-1 px-2">+ Изображение / файл</button>
                    </div>
                    <textarea name="content" id="slide-content-<?= $slide->id ?>" rows="10"
                              class="input font-mono text-sm"><?= Html::encode($slide->content ?? '') ?></textarea>
                </div>
                <div>
                    <label>Заметки (видны только вам)</label>
                    <textarea name="notes" rows="10" class="input font-mono text-sm"><?= Html::encode($slide->notes ?? '') ?></textarea>
                </div>
                <div class="col-span-2">
                    <?= Html::submitButton('Сохранить слайд', ['class' => 'btn-primary text-sm py-1.5 px-4']) ?>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->render('@app/views/admin/media/_modal') ?>