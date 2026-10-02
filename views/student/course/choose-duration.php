<?php
/** @var app\models\Course $course */
/** @var string|null $error */
use yii\helpers\Html;

$this->title = 'Начать курс';
?>

<div class="max-w-lg mx-auto">
    <div class="card">
        <h1 class="text-xl font-bold text-base-100 mb-2">
            Начать курс «<?= Html::encode($course->title) ?>»
        </h1>
        <p class="text-sm text-base-400 mb-6">
            Программа автоматически распределится на выбранный срок.
        </p>

        <?php if ($error): ?>
            <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="field-group">
                <label>Срок подготовки: <span id="duration-value" class="font-semibold text-acid-lime">3 месяца</span></label>
                <input type="range" name="duration_months" min="1" max="10" value="3"
                       class="w-full accent-acid-lime"
                       oninput="document.getElementById('duration-value').textContent = this.value + ' мес.'">
                <div class="flex justify-between text-xs text-base-400 mt-1">
                    <span>1 месяц</span><span>10 месяцев</span>
                </div>
            </div>
            <?= Html::submitButton('Начать курс', ['class' => 'btn-primary w-full py-3']) ?>
        </form>
    </div>
</div>