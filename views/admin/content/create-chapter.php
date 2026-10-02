<?php
/** @var app\models\BookChapter[] $parentChapters */
/** @var app\models\BookChapter|null $chapter */
/** @var app\models\BookSection[] $sections */
/** @var int|null $sectionId */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$isNew = !isset($chapter) || $chapter === null;
$this->title = $isNew ? 'Новая глава' : 'Глава: ' . $chapter->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/content/book']) ?>" class="hover:text-base-100 no-underline">Учебник</a>
    <span>/</span>
    <span class="text-base-100"><?= $isNew ? 'Новая глава' : Html::encode($chapter->title) ?></span>
</div>

<div class="max-w-lg card">
    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Раздел *</label>
            <select name="section_id" class="input" required>
                <?php foreach ($sections as $sec): ?>
                    <option value="<?= $sec->id ?>"
                        <?= (($isNew && $sectionId == $sec->id) || (!$isNew && $chapter->section_id == $sec->id)) ? 'selected' : '' ?>>
                        <?= Html::encode($sec->title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Название главы *</label>
            <input type="text" name="title" class="input" required autofocus
                   value="<?= $isNew ? '' : Html::encode($chapter->title) ?>">
        </div>
        <div class="field-group">
            <label>Название главы *</label>
            <input type="text" name="title" class="input" required autofocus
                value="<?= $isNew ? '' : Html::encode($chapter->title) ?>">
        </div>

        <?php if (!$isNew): ?>
        <div class="field-group">
            <label>URL темы (slug)</label>
            <input type="text" name="slug" class="input font-mono text-sm"
                value="<?= Html::encode($chapter->slug) ?>">
            <p class="text-xs text-acid-pink mt-1.5">
                Изменение URL сломает уже сохранённые ссылки на страницы этой темы — меняйте только если уверены.
            </p>
        </div>
        <?php endif; ?>
        <div class="field-group">
            <label>Родительская глава (для подглавы)</label>
            <select name="parent_id" class="input">
                <option value="">— Верхний уровень —</option>
                <?php foreach ($parentChapters as $parent): ?>
                    <option value="<?= $parent->id ?>"
                        <?= (!$isNew && $chapter->parent_id === $parent->id) ? 'selected' : '' ?>>
                        <?= Html::encode($parent->title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Порядок сортировки</label>
            <input type="number" name="sort_order" class="input"
                   value="<?= $isNew ? 0 : $chapter->sort_order ?>">
        </div>

        <div class="flex gap-3">
            <?= Html::submitButton($isNew ? 'Создать главу' : 'Сохранить', ['class' => 'btn-primary flex-1']) ?>
            <a href="<?= Url::to(['/admin/content/book']) ?>" class="btn-secondary px-4">Отмена</a>
        </div>
    </form>
</div>