<?php
/** @var yii\web\View $this */
/** @var app\models\forms\LoginForm $form */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Вход';
?>

<div class="card">

    <!-- Заголовок -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-100 mb-1">Добро пожаловать</h1>
        <p class="text-sm text-base-400">Войдите в свой аккаунт</p>
    </div>

    <!-- Flash -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success mb-5">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <?php $activeForm = ActiveForm::begin([
        'id'                     => 'login-form',
        'enableClientValidation' => false,
        'fieldConfig'            => [
            'template'     => '{input}{error}',
            'inputOptions' => ['class' => 'input'],
            'errorOptions' => ['class' => 'field-error', 'tag' => 'p'],
        ],
    ]) ?>

        <!-- Логин -->
        <div class="field-group">
            <label for="loginform-username">Логин</label>
            <?= $activeForm->field($form, 'username')->textInput([
                'class'        => 'input' . ($form->hasErrors('username') ? ' input-error' : ''),
                'placeholder'  => 'Введите логин',
                'autofocus'    => true,
                'autocomplete' => 'username',
                'id'           => 'loginform-username',
            ])->label(false) ?>
        </div>

        <!-- Пароль -->
        <div class="field-group">
            <div class="flex items-center justify-between mb-1.5">
                <label for="loginform-password" class="mb-0">Пароль</label>
                <a href="/password-reset"
                   class="text-xs text-base-400 hover:text-acid-lime no-underline transition-colors duration-150">
                    Забыли пароль?
                </a>
            </div>
            <?= $activeForm->field($form, 'password')->passwordInput([
                'class'        => 'input' . ($form->hasErrors('password') ? ' input-error' : ''),
                'placeholder'  => 'Введите пароль',
                'autocomplete' => 'current-password',
                'id'           => 'loginform-password',
            ])->label(false) ?>
        </div>

        <!-- Запомнить меня -->
        <div class="mb-6">
            <label class="checkbox-label">
                <?= Html::activeCheckbox($form, 'rememberMe', [
                    'label' => false,
                    'id'    => 'loginform-rememberme',
                ]) ?>
                <span>Запомнить меня на 30 дней</span>
            </label>
        </div>

        <!-- Кнопка -->
        <?= Html::submitButton('Войти в аккаунт', [
            'class' => 'btn-primary w-full py-3',
        ]) ?>

    <?php ActiveForm::end() ?>
<p class="text-center text-sm text-base-400 mt-4">
    Нет аккаунта? <a href="/signup" class="text-acid-lime">Зарегистрироваться самостоятельно</a>
</p>
</div>