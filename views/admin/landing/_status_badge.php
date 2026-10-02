<?php
/** @var app\models\Landing $landing */
use yii\helpers\Url;
use app\models\Landing;
?>
<span hx-get="<?= Url::to(['/admin/landing/toggle-status', 'id' => $landing->id]) ?>"
      hx-target="this"
      hx-swap="outerHTML"
      class="<?= $landing->status === Landing::STATUS_PUBLISHED ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer">
    <?= $landing->status === Landing::STATUS_PUBLISHED ? 'Опубликован' : 'Черновик' ?>
</span>