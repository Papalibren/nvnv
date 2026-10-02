<?php
/** @var yii\web\View $this */
/** @var string|null $message */
use yii\helpers\Html;
?>

<div class="card text-center">
    <div class="text-4xl mb-4">⚠️</div>
    <h1 class="text-xl font-bold text-base-100 mb-2">Ссылка недействительна</h1>
    <p class="text-base-400 text-sm mb-6">
        <?= Html::encode($message ?? 'Эта ссылка устарела или уже была использована.') ?>
    </p>
    <a href="/login" class="btn-secondary">Перейти ко входу</a>
</div>