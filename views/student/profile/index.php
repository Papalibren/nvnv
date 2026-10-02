<?php
/** @var app\models\User $student */
/** @var app\models\User|null $teacher */
/** @var string|null $error */
/** @var string|null $success */
use yii\helpers\Html;

$this->title = 'Профиль';
?>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-base-100 mb-6">Профиль</h1>

    <?php if ($success): ?>
        <div class="alert-success mb-4"><?= Html::encode($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
    <?php endif; ?>

    <?php if ($teacher): ?>
        <div class="card mb-4">
            <p class="text-xs text-base-400 mb-1">Ваш учитель</p>
            <p class="font-semibold text-base-100"><?= Html::encode($teacher->name) ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="field-group">
                <label>Псевдоним для рейтинга</label>
                <input type="text" name="display_name" class="input"
                       placeholder="Как вас показывать в публичном рейтинге"
                       value="<?= Html::encode($student->display_name ?? '') ?>">
                <p class="text-xs text-base-400 mt-1">
                    Если не заполнено — в рейтинге будет «Ученик #<?= $student->id ?>»
                </p>
            </div>

            <div class="field-group">
                <label>Логин</label>
                <input type="text" name="username" class="input"
                       value="<?= Html::encode($student->username ?? '') ?>">
            </div>

            <div class="field-group">
                <label>Email</label>
                <input type="email" name="email" class="input"
                       value="<?= Html::encode($student->email ?? '') ?>">
            </div>

            <div class="field-group">
                <label>Новый пароль (оставьте пустым, если не меняете)</label>
                <input type="password" name="new_password" class="input" minlength="6">
            </div>

            <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full']) ?>
        </form>
    </div>
</div>