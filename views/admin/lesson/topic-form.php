<?php
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новая тема';
?>
<div class="max-w-lg">
    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="<?= Url::to(['/admin/lesson/index']) ?>" class="hover:text-base-100 no-underline">Уроки</a>
        <span>/</span><span class="text-base-100">Новая тема</span>
    </div>
    <div class="card">
        <?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>
        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="field-group">
                <label>Название темы *</label>
                <input type="text" name="title" class="input" required autofocus placeholder="Например: Python">
            </div>
            <div class="field-group">
                <label>Описание</label>
                <textarea name="description" class="input" rows="2"></textarea>
            </div>
            <?= Html::submitButton('Создать тему', ['class' => 'btn-primary w-full']) ?>
        </form>
    </div>
</div>