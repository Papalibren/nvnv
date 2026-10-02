<?php
/** @var app\models\BookSection $section */
use yii\helpers\Url;
?>
<span hx-get="<?= Url::to(['/admin/content/toggle-section-status', 'id' => $section->id]) ?>"
      hx-target="this"
      hx-swap="outerHTML"
      class="<?= $section->is_published ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer"
      title="Нажмите чтобы <?= $section->is_published ? 'скрыть' : 'опубликовать' ?>">
    <?= $section->is_published ? 'Опубликован' : 'Скрыт' ?>
</span>