<?php
/** @var app\models\User $student */
/** @var array $items */
use yii\helpers\Html;

$this->title = 'История обучения';
?>

<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-100">История обучения</h1>
        <p class="text-base-400">Ученик: <?= Html::encode($student->name) ?></p>
    </div>

    <?= $this->render('@app/views/timeline/_items', ['items' => $items]) ?>
</div>