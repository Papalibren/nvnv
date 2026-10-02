<?php
/** @var app\models\LessonTopic[] $topics */
/** @var array $lessonsByTopic */
/** @var app\models\Lesson[] $ungrouped */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Уроки';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Уроки</h1>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/admin/lesson/create-topic']) ?>" class="btn-secondary">+ Тема</a>
        <a href="<?= Url::to(['/admin/lesson/create']) ?>" class="btn-primary">+ Урок</a>
    </div>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php foreach ($topics as $topic): ?>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <a href="<?= Url::to(['/admin/lesson/move-topic', 'id' => $topic->id, 'direction' => 'up']) ?>" class="btn-ghost text-xs">↑</a>
                <a href="<?= Url::to(['/admin/lesson/move-topic', 'id' => $topic->id, 'direction' => 'down']) ?>" class="btn-ghost text-xs">↓</a>
                <h2 class="text-base font-semibold text-base-100"><?= Html::encode($topic->title) ?></h2>
            </div>
            <a href="<?= Url::to(['/admin/lesson/create', 'topicId' => $topic->id]) ?>" class="text-xs text-acid-lime no-underline">+ урок в эту тему</a>
        </div>

        <?php $lessons = $lessonsByTopic[$topic->id] ?? []; ?>
        <?php if (empty($lessons)): ?>
            <p class="text-sm text-base-400">Уроков пока нет.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($lessons as $lesson): ?>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-700">
                        <div class="flex items-center gap-2">
                            <a href="<?= Url::to(['/admin/lesson/move-lesson', 'id' => $lesson->id, 'direction' => 'up']) ?>" class="btn-ghost text-xs">↑</a>
                            <a href="<?= Url::to(['/admin/lesson/move-lesson', 'id' => $lesson->id, 'direction' => 'down']) ?>" class="btn-ghost text-xs">↓</a>
                            <a href="<?= Url::to(['/admin/lesson/view', 'id' => $lesson->id]) ?>" class="text-sm font-medium text-base-100 no-underline hover:text-acid-lime">
                                <?= Html::encode($lesson->title) ?>
                            </a>
                        </div>
                        <span class="text-xs text-base-400"><?= $lesson->getSlideDecks()->count() ?> колод слайдов</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($ungrouped): ?>
    <div class="mb-6">
        <h2 class="text-base font-semibold text-base-100 mb-3">Без темы</h2>
        <div class="space-y-2">
            <?php foreach ($ungrouped as $lesson): ?>
                <a href="<?= Url::to(['/admin/lesson/view', 'id' => $lesson->id]) ?>"
                   class="block p-3 rounded-lg bg-white border border-base-700 text-sm font-medium text-base-100 no-underline hover:text-acid-lime">
                    <?= Html::encode($lesson->title) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($topics) && empty($ungrouped)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400 mb-4">Уроков пока нет.</p>
        <a href="<?= Url::to(['/admin/lesson/create-topic']) ?>" class="btn-primary">Создать первую тему</a>
    </div>
<?php endif; ?>