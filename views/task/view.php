<?php
/** @var yii\web\View $this */
/** @var app\models\Task $task */
use app\widgets\TaskWidget;
use app\assets\KatexAsset;
use yii\helpers\Url;

KatexAsset::register($this);
?>

<div class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-6">
        <a href="<?= Url::to(['/tasks']) ?>"
           class="text-sm text-base-400 hover:text-acid-lime no-underline">
            ← Все задачи
        </a>
    </div>

    <?= TaskWidget::widget(['task' => $task, 'mode' => 'public']) ?>

</div>