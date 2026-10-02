<?php
/** @var yii\web\View $this */
/** @var app\models\forms\RegisterForm $form */
/** @var app\models\InviteToken $invite */
/** @var string|null $error */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="card">
    <h1 class="text-2xl font-bold text-base-100 mb-1">Активация аккаунта</h1>
    <p class="text-base-400 text-sm mb-6">
        Привет, <strong><?= Html::encode($invite->student->name) ?></strong>!
        Установите логин и пароль для входа.
    </p>

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
            <?= Html::label('Логин', 'registerform-username') ?>
            <?= $activeForm->field($form, 'username')->textInput([
                'placeholder' => 'Только латиница, цифры, _ и -',
                'autofocus'   => true,
            ])->label(false) ?>
        </div>

        <div class="field-group">
            <?= Html::label('Email', 'registerform-email') ?>
            <?= $activeForm->field($form, 'email')->input('email', [
                'placeholder' => 'Для восстановления пароля (необязательно)',
            ])->label(false) ?>
        </div>

        <div class="field-group">
            <?= Html::label('Пароль', 'registerform-password') ?>
            <?= $activeForm->field($form, 'password')->passwordInput([
                'placeholder' => 'Минимум 6 символов',
            ])->label(false) ?>
        </div>

        <div class="field-group">
            <?= Html::label('Подтверждение пароля', 'registerform-password_confirm') ?>
            <?= $activeForm->field($form, 'password_confirm')->passwordInput([
                'placeholder' => 'Повторите пароль',
            ])->label(false) ?>
        </div>

        <?= Html::submitButton('Создать аккаунт', ['class' => 'btn-primary w-full mt-2']) ?>

    <?php ActiveForm::end() ?>
</div>