<?php
/** @var yii\web\View $this */
/** @var app\models\forms\ResetPasswordForm $form */
/** @var string|null $error */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="card">
    <h1 class="text-2xl font-bold text-base-100 mb-1">Новый пароль</h1>
    <p class="text-base-400 text-sm mb-6">Введите новый пароль.</p>

    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <?php $activeForm = ActiveForm::begin([
        'enableClientValidation' => false,
        'fieldConfig' => [
            'template'     => '{input}{error}',
            'errorOptions' => ['class' => 'field-error'],
        ],
    ]) ?>

        <div class="field-group">
            <?= Html::label('Новый пароль', 'resetpasswordform-password') ?>
            <?= $activeForm->field($form, 'password')->passwordInput([
                'placeholder' => 'Минимум 6 символов',
                'autofocus'   => true,
            ])->label(false) ?>
        </div>

        <div class="field-group">
            <?= Html::label('Подтверждение', 'resetpasswordform-password_confirm') ?>
            <?= $activeForm->field($form, 'password_confirm')->passwordInput([
                'placeholder' => 'Повторите пароль',
            ])->label(false) ?>
        </div>

        <?= Html::submitButton('Сохранить пароль', ['class' => 'btn-primary w-full']) ?>

    <?php ActiveForm::end() ?>
</div>