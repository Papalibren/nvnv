<?php

/** @var app\models\BookSection[] $sections */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Разделы учебника';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Разделы учебника</h1>
        <p class="text-sm text-base-400 mt-0.5">
            Например: «Информатика», «Python» — независимые книги
        </p>
    </div>
    <a href="<?= Url::to(['/admin/content/create-section']) ?>" class="btn-primary">
        + Новый раздел
    </a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4">
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-4">
    <?php foreach ($sections as $section): ?>
        <div id="section-<?= $section->id ?>" class="card">
            <div class="flex items-start justify-between mb-2">
                <h3 class="font-semibold text-base-100"><?= Html::encode($section->title) ?></h3>
                <span class="badge-gray"><?= $section->getPageCount() ?> стр.</span>
            </div>
            <?php if ($section->description): ?>
                <p class="text-sm text-base-400 mb-3"><?= Html::encode($section->description) ?></p>
            <?php endif; ?>
            <p class="text-xs text-base-400 mb-4">
                <span id="chapter-status-<?= $section->id ?>">
                    <?= $this->render('_section_status', ['section' => $section]) ?>
                </span>
            </p>

            <div class="flex gap-2">
                <a href="<?= Url::to(['/admin/content/book', 'sectionId' => $section->id]) ?>"
                    class="btn-secondary text-xs py-1.5 px-3 flex-1 text-center">
                    Открыть
                </a>
                <a href="<?= Url::to(['/admin/content/update-section', 'id' => $section->id]) ?>"
                    class="btn-ghost text-xs py-1.5 px-3">
                    Изменить
                </a>
                <button type="button"
                    class="btn-ghost text-xs py-1.5 px-3 text-acid-pink"
                    hx-delete="<?= Url::to(['/admin/content/delete-section', 'id' => $section->id]) ?>"
                    hx-target="#section-<?= $section->id ?>"
                    hx-swap="outerHTML swap:300ms"
                    hx-confirm="Удалить раздел «<?= Html::encode($section->title) ?>»?">
                    ✕
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>