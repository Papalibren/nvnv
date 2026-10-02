<?php
/** @var app\models\BookSection|null $section */
/** @var string|null $error */
/** @var bool $isNew */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $isNew ? 'Новый раздел' : 'Раздел: ' . $section->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/content/book-sections']) ?>" class="hover:text-base-100 no-underline">Разделы</a>
    <span>/</span><span class="text-base-100"><?= $isNew ? 'Новый' : Html::encode($section->title) ?></span>
</div>

<div class="max-w-lg card">
    <?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Название раздела *</label>
            <input type="text" name="title" class="input" required autofocus
                   placeholder="Например: Python или Информатика"
                   value="<?= $isNew ? '' : Html::encode($section->title) ?>">
        </div>

        <div class="field-group">
            <label>URL раздела *</label>
            <div class="flex items-center gap-1">
                <span class="text-sm text-base-400 shrink-0">/book/</span>
                <input type="text" name="slug" class="input" required
                       value="<?= $isNew ? '' : Html::encode($section->slug) ?>">
            </div>
            <?php if (!$isNew): ?>
                <p class="text-xs text-acid-pink mt-1.5">
                    Изменение URL сломает уже сохранённые ссылки на этот раздел — меняйте только если уверены.
                </p>
            <?php else: ?>
                <p class="text-xs text-base-400 mt-1.5">Оставьте пустым — сгенерируется из названия.</p>
            <?php endif; ?>
        </div>

        <div class="field-group">
            <label>Описание</label>
            <textarea name="description" class="input" rows="2"><?= $isNew ? '' : Html::encode($section->description ?? '') ?></textarea>
        </div>

        <div class="field-group">
            <label>Порядок сортировки</label>
            <input type="number" name="sort_order" class="input" value="<?= $isNew ? 0 : $section->sort_order ?>">
        </div>

        <div class="flex gap-3">
            <?= Html::submitButton($isNew ? 'Создать раздел' : 'Сохранить', ['class' => 'btn-primary flex-1']) ?>
            <a href="<?= Url::to(['/admin/content/book-sections']) ?>" class="btn-secondary px-4">Отмена</a>
        </div>
    </form>
</div>