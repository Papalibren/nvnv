<?php
/** @var yii\web\View $this */
/** @var app\models\forms\PasswordResetRequestForm $form */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="card">
    <h1 class="text-2xl font-bold text-base-100 mb-1">Восстановление пароля</h1>
    <p class="text-base-400 text-sm mb-6">
        Введите email — отправим ссылку для сброса пароля.
    </p>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success mb-4">
            <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        </div>
    <?php else: ?>

        <?php $activeForm = ActiveForm::begin([
            'enableClientValidation' => false,
            'fieldConfig' => [
                'template'     => '{input}{error}',
                'errorOptions' => ['class' => 'field-error'],
            ],
        ]) ?>

            <div class="field-group">
                <?= Html::label('Email', 'passwordresetrequestform-email') ?>
                <?= $activeForm->field($form, 'email')->input('email', [
                    'placeholder' => 'Ваш email',
                    'autofocus'   => true,
                ])->label(false) ?>
            </div>

            <?= Html::submitButton('Отправить ссылку', ['class' => 'btn-primary w-full']) ?>

        <?php ActiveForm::end() ?>

    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="/login" class="text-sm text-base-400 hover:text-acid-cyan">
            ← Вернуться ко входу
        </a>
    </div>
</div>