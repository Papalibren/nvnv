<?php
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новая группа';
?>

<div class="max-w-lg">
    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="<?= Url::to(['/teacher/group/index']) ?>"
           class="hover:text-base-100 no-underline">Группы</a>
        <span>/</span>
        <span class="text-base-100">Новая группа</span>
    </div>

    <div class="card">
        <?php if ($error): ?>
            <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="field-group">
                <label>Название группы</label>
                <input type="text" name="name" class="input" required autofocus>
            </div>

            <div class="field-group">
                <label>Описание (необязательно)</label>
                <textarea name="description" class="input" rows="3"></textarea>
            </div>

            <?= Html::submitButton('Создать группу', ['class' => 'btn-primary w-full']) ?>
        </form>
    </div>
</div>