<?php
/** @var app\models\Course[] $courses */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Курсы';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Курсы</h1>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/admin/course/process-unlocks']) ?>" class="btn-secondary">
            Проверить разблокировки сейчас
        </a>
        <a href="<?= Url::to(['/admin/course/create']) ?>" class="btn-primary">+ Новый курс</a>
    </div>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($courses)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400">Курсов пока нет.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-2 gap-4">
        <?php foreach ($courses as $course): ?>
            <a href="<?= Url::to(['/admin/course/view', 'id' => $course->id]) ?>" class="card-interactive no-underline">
                <h3 class="font-semibold text-base-100 mb-1"><?= Html::encode($course->title) ?></h3>
                <?php if ($course->description): ?>
                    <p class="text-sm text-base-400 mb-3"><?= Html::encode($course->description) ?></p>
                <?php endif; ?>
                <span class="badge-gray"><?= $course->getCourseLessons()->count() ?> тем</span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>