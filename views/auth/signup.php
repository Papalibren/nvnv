<?php
/** @var string|null $error */
use yii\helpers\Html;
?>

<div class="card">
    <h1 class="text-2xl font-bold text-base-100 mb-1">Самостоятельная регистрация</h1>
    <p class="text-sm text-base-400 mb-6">
        Начните готовиться к ЕГЭ в своём темпе — бесплатно
    </p>

    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Ваше имя</label>
            <input type="text" name="name" class="input" required autofocus>
        </div>

        <div class="field-group">
            <label>Логин</label>
            <input type="text" name="username" class="input" required>
        </div>

        <div class="field-group">
            <label>Email (необязательно, для восстановления пароля)</label>
            <input type="email" name="email" class="input">
        </div>

        <div class="field-group">
            <label>Пароль</label>
            <input type="password" name="password" class="input" minlength="6" required>
        </div>

        <?= Html::submitButton('Создать аккаунт', ['class' => 'btn-primary w-full py-3']) ?>
    </form>

    <p class="text-center text-sm text-base-400 mt-4">
        Уже есть аккаунт? <a href="/login" class="text-acid-lime">Войти</a>
    </p>
</div>