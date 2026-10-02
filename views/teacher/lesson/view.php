<?php
/** @var app\models\Lesson $lesson */
/** @var app\models\BookPage[] $bookPages */
/** @var app\models\SlideDeck[] $decks */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;

$this->title = $lesson->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/lesson/index']) ?>" class="hover:text-base-100 no-underline">Уроки</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($lesson->title) ?></span>
</div>

<h1 class="text-2xl font-bold text-base-100 mb-6"><?= Html::encode($lesson->title) ?></h1>

<?php if ($lesson->isInfo()): ?>
    <div class="card mb-6">
        <h2 class="text-base font-semibold text-base-100 mb-3">Текст урока</h2>
        <div class="prose-task text-sm"><?= ContentRenderer::render($lesson->info_content ?? '') ?></div>
    </div>
<?php elseif ($bookPages): ?>
    <div class="card mb-6">
        <h2 class="text-base font-semibold text-base-100 mb-3">Теория</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($bookPages as $page): ?>
                <?php $section = $page->chapter->section ?? null; ?>
                <?php if ($section): ?>
                    <a href="/book/<?= $section->slug ?>/<?= $page->slug ?>" target="_blank"
                       class="text-sm text-acid-lime hover:text-acid-violet no-underline">📖 <?= Html::encode($page->title) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Слайды</h2>
    <?php if (empty($decks)): ?>
        <p class="text-base-400 text-sm">Слайдов к этому уроку пока нет.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($decks as $deck): ?>
                <div class="flex items-center justify-between p-3 rounded-lg bg-base-900">
                    <p class="font-medium text-base-100"><?= Html::encode($deck->title) ?></p>
                    <a href="<?= Url::to(['/teacher/slide/present', 'id' => $deck->id]) ?>" target="_blank" class="btn-primary text-xs py-1.5 px-3">
                        Начать презентацию
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>