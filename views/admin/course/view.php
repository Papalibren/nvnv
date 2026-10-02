<?php
/** @var app\models\Course $course */
/** @var app\models\CourseLesson[] $lessons */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $course->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/course/index']) ?>" class="hover:text-base-100 no-underline">Курсы</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($course->title) ?></span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100"><?= Html::encode($course->title) ?></h1>
        <p class="text-sm text-base-400 mt-0.5">/<?= $course->slug ?></p>
    </div>
    <a href="<?= Url::to(['/admin/course/create-lesson', 'courseId' => $course->id]) ?>" class="btn-primary">
        + Добавить тему
    </a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($lessons)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400 mb-4">В курсе пока нет тем.</p>
        <a href="<?= Url::to(['/admin/course/create-lesson', 'courseId' => $course->id]) ?>" class="btn-primary">
            Добавить первую тему
        </a>
    </div>
<?php else: ?>
    <div class="space-y-2">
        <?php foreach ($lessons as $i => $cl): ?>
            <div id="cl-<?= $cl->id ?>" class="card flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-base-900 flex items-center justify-center text-xs font-bold text-base-400 shrink-0">
                        <?= $i + 1 ?>
                    </span>
                    <div>
                        <p class="font-medium text-base-100">
                            <?= Html::encode($cl->lesson->title ?? '—') ?>
                            <?php if ($cl->isCheckpoint()): ?>
                                <span class="badge-violet ml-1">Чекпоинт: <?= Html::encode($cl->checkpointExam->title ?? '') ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= Url::to(['/admin/course/move-lesson', 'id' => $cl->id, 'direction' => 'up']) ?>"
                    class="btn-ghost text-xs">↑</a>
                    <a href="<?= Url::to(['/admin/course/move-lesson', 'id' => $cl->id, 'direction' => 'down']) ?>"
                    class="btn-ghost text-xs">↓</a>
                    <a href="<?= Url::to(['/admin/course/update-lesson', 'id' => $cl->id]) ?>" class="btn-ghost text-xs">
                        Изменить
                    </a>
                    <button class="btn-ghost text-xs text-acid-pink"
                            hx-delete="<?= Url::to(['/admin/course/delete-lesson', 'id' => $cl->id]) ?>"
                            hx-target="#cl-<?= $cl->id ?>"
                            hx-swap="outerHTML swap:300ms"
                            hx-confirm="Удалить тему из курса?">
                        Удалить
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-sm text-base-400">
        Контрольные точки (публичные экзамены) назначаются на странице
        <a href="<?= Url::to(['/admin/exam/assign-checkpoint']) ?>" class="text-acid-lime">Контрольные точки курса →</a>
    </div>
<?php endif; ?>