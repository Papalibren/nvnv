<?php
/** @var app\models\BookPage $page */
use yii\helpers\Url;
?>
<span hx-get="<?= Url::to(['/admin/content/toggle-page-status', 'id' => $page->id]) ?>"
      hx-target="#page-status-<?= $page->id ?>"
      hx-swap="innerHTML"
      class="<?= $page->isPublished() ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer"
      title="Нажмите чтобы <?= $page->isPublished() ? 'снять с публикации' : 'опубликовать' ?>">
    <?= $page->isPublished() ? 'Опубликована' : 'Черновик' ?>
</span>