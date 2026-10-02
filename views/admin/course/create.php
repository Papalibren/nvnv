<?php
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новый курс';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/course/index']) ?>" class="hover:text-base-100 no-underline">Курсы</a>
    <span>/</span><span class="text-base-100">Новый</span>
</div>

<div class="max-w-lg card">
    <?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Название курса *</label>
            <input type="text" name="title" class="input" required autofocus
                   placeholder="Например: Самостоятельная подготовка к ЕГЭ">
        </div>

        <div class="field-group">
            <label>Описание</label>
            <textarea name="description" class="input" rows="3"></textarea>
        </div>

        <?= Html::submitButton('Создать курс', ['class' => 'btn-primary w-full']) ?>
    </form>
</div>