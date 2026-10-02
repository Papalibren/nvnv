<?php
/** @var yii\web\View $this */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новый учитель';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/user/index']) ?>"
       class="hover:text-base-100 transition-colors duration-150 no-underline">
        Пользователи
    </a>
    <span>/</span>
    <span class="text-base-100">Новый учитель</span>
</div>

<div class="max-w-lg card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Данные учителя</h2>

    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Имя</label>
            <input type="text" name="name" class="input" required autofocus>
        </div>

        <div class="field-group">
            <label>Логин</label>
            <input type="text" name="username" class="input" required>
        </div>

        <div class="field-group">
            <label>Пароль</label>
            <input type="password" name="password" class="input" minlength="6" required>
        </div>

        <?= Html::submitButton('Создать учителя', ['class' => 'btn-primary w-full']) ?>
    </form>
</div>