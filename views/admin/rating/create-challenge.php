<?php
/** @var app\models\Task[] $tasks */
/** @var string|null $error */
use yii\helpers\Html;

$this->title = 'Новая публичная задача';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Новая публичная задача</h1>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<div class="max-w-lg card">
    <form method="post">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="field-group">
            <label>Название (для списка)</label>
            <input type="text" name="title" class="input" required autofocus placeholder="Задача недели №1">
        </div>

        <div class="field-group">
            <label>Задача из банка</label>
            <select name="task_id" class="input" required>
                <?php foreach ($tasks as $t): ?>
                    <option value="<?= $t->id ?>">
                        #<?= $t->id ?> — <?= $t->task_number ? 'Задание ' . $t->task_number : 'Без номера' ?> —
                        <?= Html::encode(mb_substr(strip_tags($t->content), 0, 60)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Баллы за решение</label>
            <input type="number" name="points" class="input" value="20">
        </div>

        <div class="field-group">
            <label>Открытие</label>
            <input type="datetime-local" name="opens_at" class="input" required>
        </div>

        <div class="field-group">
            <label>Закрытие</label>
            <input type="datetime-local" name="closes_at" class="input" required>
        </div>

        <?= Html::submitButton('Создать', ['class' => 'btn-primary w-full']) ?>
    </form>
</div>