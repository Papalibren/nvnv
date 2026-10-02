<?php
/** @var app\models\LessonTopic[] $topics */
/** @var array $lessonsByTopic */
/** @var app\models\Lesson[] $ungrouped */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Уроки';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Уроки</h1>

<?php foreach ($topics as $topic): ?>
    <?php $lessons = $lessonsByTopic[$topic->id] ?? []; ?>
    <?php if ($lessons): ?>
        <div class="mb-6">
            <h2 class="text-base font-semibold text-base-100 mb-3"><?= Html::encode($topic->title) ?></h2>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($lessons as $lesson): ?>
                    <a href="<?= Url::to(['/teacher/lesson/view', 'id' => $lesson->id]) ?>" class="card-interactive no-underline">
                        <p class="font-medium text-base-100"><?= Html::encode($lesson->title) ?></p>
                        <p class="text-xs text-base-400 mt-1">
                            <?= $lesson->isInfo() ? 'Информационный' : 'Практический' ?> ·
                            <?= $lesson->getSlideDecks()->count() ?> колод слайдов
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($ungrouped): ?>
    <div class="mb-6">
        <h2 class="text-base font-semibold text-base-100 mb-3">Без темы</h2>
        <div class="grid grid-cols-2 gap-3">
            <?php foreach ($ungrouped as $lesson): ?>
                <a href="<?= Url::to(['/teacher/lesson/view', 'id' => $lesson->id]) ?>" class="card-interactive no-underline">
                    <p class="font-medium text-base-100"><?= Html::encode($lesson->title) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>