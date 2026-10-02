<?php
/** @var app\models\BookSection[] $sections */
/** @var app\models\BookSection|null $currentSection */
/** @var app\models\BookChapter[] $chapters */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $currentSection ? $currentSection->title : 'Учебник';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Учебник</h1>
        <p class="text-sm text-base-400 mt-0.5">
            <a href="<?= Url::to(['/admin/content/book-sections']) ?>" class="text-acid-lime">
                Управление разделами →
            </a>
        </p>
    </div>
    <?php if ($currentSection): ?>
    <div class="flex gap-3">
        <a href="<?= Url::to(['/admin/content/create-chapter', 'sectionId' => $currentSection->id]) ?>"
           class="btn-secondary">+ Глава</a>
        <a href="<?= Url::to(['/admin/content/create-page']) ?>" class="btn-primary">+ Страница</a>
    </div>
    <?php endif; ?>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<!-- Табы разделов -->
<?php if (empty($sections)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400 mb-4">Разделов пока нет.</p>
        <a href="<?= Url::to(['/admin/content/create-section']) ?>" class="btn-primary">
            Создать первый раздел
        </a>
    </div>
<?php else: ?>
    <div class="flex gap-2 mb-6 flex-wrap">
        <?php foreach ($sections as $section): ?>
            <a href="<?= Url::to(['/admin/content/book', 'sectionId' => $section->id]) ?>"
               class="<?= $currentSection && $currentSection->id === $section->id
                   ? 'btn-primary' : 'btn-secondary' ?> text-sm py-2 px-4 no-underline">
                <?= Html::encode($section->title) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($chapters)): ?>
        <div class="card text-center py-16">
            <p class="text-base-400 mb-4">В разделе «<?= Html::encode($currentSection->title) ?>» пока нет глав.</p>
            <a href="<?= Url::to(['/admin/content/create-chapter', 'sectionId' => $currentSection->id]) ?>"
               class="btn-primary">Создать главу</a>
        </div>
    <?php else: ?>
        <div class="space-y-4" id="chapter-list">
            <?php foreach ($chapters as $chapter): ?>
                <?= $this->render('_chapter_row', ['chapter' => $chapter, 'depth' => 0]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>