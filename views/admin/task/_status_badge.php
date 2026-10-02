<?php
/** @var app\models\Task $task */
use yii\helpers\Url;
?>
<span hx-get="<?= Url::to(['/admin/task/toggle-status', 'id' => $task->id]) ?>"
      hx-target="this"
      hx-swap="outerHTML"
      class="<?= $task->status === 'published' ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer"
      title="Нажмите чтобы изменить">
    <?= $task->status === 'published' ? 'Опубликована' : 'Черновик' ?>
</span>