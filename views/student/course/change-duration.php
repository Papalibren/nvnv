<?php
/** @var app\models\CourseEnrollment $enrollment */
/** @var string|null $error */
use yii\helpers\Html;

$this->title = 'Изменить срок';
?>

<div class="max-w-lg mx-auto card">
    <h1 class="text-xl font-bold text-base-100 mb-2">Изменить срок подготовки</h1>
    <p class="text-sm text-base-400 mb-6">
        Текущий срок: <?= $enrollment->duration_months ?> мес.
        Это можно сделать только один раз — оставшиеся темы пересчитаются под новый срок.
    </p>

    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Новый срок: <span id="duration-value" class="font-semibold text-acid-lime"><?= $enrollment->duration_months ?> мес.</span></label>
            <input type="range" name="duration_months" min="1" max="10" value="<?= $enrollment->duration_months ?>"
                   class="w-full accent-acid-lime"
                   oninput="document.getElementById('duration-value').textContent = this.value + ' мес.'">
        </div>

        <?= Html::submitButton('Сохранить новый срок', ['class' => 'btn-primary w-full py-3']) ?>
    </form>
</div>