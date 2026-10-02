<?php
/** @var app\models\BookChapter $chapter */
use yii\helpers\Url;
?>
<span hx-get="<?= Url::to(['/admin/content/toggle-chapter-status', 'id' => $chapter->id]) ?>"
      hx-target="this"
      hx-swap="outerHTML"
      class="<?= $chapter->is_published ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer"
      title="Нажмите чтобы <?= $chapter->is_published ? 'скрыть' : 'опубликовать' ?>">
    <?= $chapter->is_published ? 'Опубликована' : 'Скрыта' ?>
</span>