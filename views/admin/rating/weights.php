<?php
/** @var app\models\RatingWeightConfig $config */
use yii\helpers\Html;

$this->title = 'Веса рейтинга';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Веса компонентов рейтинга</h1>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="max-w-lg card">
    <p class="text-sm text-base-400 mb-4">
        Указывайте относительные веса — не обязательно чтобы сумма была ровно 100,
        система нормализует пропорции автоматически.
    </p>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Курс (самоподготовка)</label>
            <input type="number" name="course_weight" class="input" value="<?= $config->course_weight ?>">
        </div>
        <div class="field-group">
            <label>Работа с репетитором</label>
            <input type="number" name="tutoring_weight" class="input" value="<?= $config->tutoring_weight ?>">
        </div>
        <div class="field-group">
            <label>Публичные задачи</label>
            <input type="number" name="public_task_weight" class="input" value="<?= $config->public_task_weight ?>">
        </div>
        <div class="field-group">
            <label>Публичные экзамены</label>
            <input type="number" name="public_exam_weight" class="input" value="<?= $config->public_exam_weight ?>">
        </div>

        <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full']) ?>
    </form>
</div>