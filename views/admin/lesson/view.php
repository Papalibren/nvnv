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
    <a href="<?= Url::to(['/admin/lesson/index']) ?>" class="hover:text-base-100 no-underline">Уроки</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($lesson->title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100"><?= Html::encode($lesson->title) ?></h1>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/admin/lesson/update', 'id' => $lesson->id]) ?>" class="btn-secondary">Изменить урок</a>
        <button type="button"
                class="btn-secondary text-acid-pink"
                onclick="if(confirm('Удалить урок «<?= Html::encode(addslashes($lesson->title)) ?>» вместе со слайдами?')) {
                    fetch('<?= Url::to(['/admin/lesson/delete', 'id' => $lesson->id]) ?>', {
                        method: 'DELETE',
                        headers: {'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content}
                    }).then(r => r.text()).then(text => {
                        if (text) { alert(text); } else { window.location.href = '<?= Url::to(['/admin/lesson/index']) ?>'; }
                    });
                }">
            Удалить
        </button>
    </div>
</div>

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
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-base-100">Слайды</h2>
        <a href="<?= Url::to(['/admin/slide/create', 'lessonId' => $lesson->id]) ?>" class="btn-primary text-sm">+ Новая колода слайдов</a>
    </div>

    <?php if (empty($decks)): ?>
        <p class="text-base-400 text-sm">Слайдов пока нет.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($decks as $deck): ?>
                <div class="flex items-center justify-between p-3 rounded-lg bg-base-900">
                    <div>
                        <p class="font-medium text-base-100"><?= Html::encode($deck->title) ?></p>
                        <p class="text-xs text-base-400"><?= $deck->getSlides()->count() ?> слайдов</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="<?= Url::to(['/teacher/slide/present', 'id' => $deck->id]) ?>" target="_blank" class="btn-primary text-xs py-1.5 px-3">Презентация</a>
                        <a href="<?= Url::to(['/admin/slide/edit', 'id' => $deck->id]) ?>" class="btn-secondary text-xs py-1.5 px-3">Редактировать</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>