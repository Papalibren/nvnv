<?php
/** @var app\models\Lead $lead */
use yii\helpers\Url;
?>
<span hx-get="<?= Url::to(['/admin/lead/toggle-processed', 'id' => $lead->id]) ?>"
      hx-target="#lead-processed-<?= $lead->id ?>"
      hx-swap="innerHTML"
      class="<?= $lead->is_processed ? 'badge-gray' : 'badge-indigo' ?> cursor-pointer">
    <?= $lead->is_processed ? 'Обработана' : 'Новая' ?>
</span>