<?php
/** @var app\models\SlideDeck[] $decks */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Слайды';
?>
<h1 class="text-2xl font-bold text-base-100 mb-6">Все колоды слайдов</h1>

<?php if (empty($decks)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Слайдов пока нет — создайте их со страницы урока.</p></div>
<?php else: ?>
    <div class="space-y-2">
        <?php foreach ($decks as $deck): ?>
            <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-700">
                <div>
                    <p class="font-medium text-base-100"><?= Html::encode($deck->title) ?></p>
                    <p class="text-xs text-base-400"><?= Html::encode($deck->lesson->title ?? '') ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?= Url::to(['/teacher/slide/present', 'id' => $deck->id]) ?>" target="_blank" class="btn-primary text-xs py-1.5 px-3">Презентация</a>
                    <a href="<?= Url::to(['/teacher/slide/edit', 'id' => $deck->id]) ?>" class="btn-secondary text-xs py-1.5 px-3">Редактировать</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>