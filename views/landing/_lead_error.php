<?php use yii\helpers\Html; ?>
<div class="alert-error mb-4">
    <?= Html::encode($message) ?>
</div>
<?= $this->render('_lead_form_fields', ['landingId' => Yii::$app->request->post('landing_id') ?? null]) ?>