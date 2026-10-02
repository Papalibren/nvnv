<?php
/** @var app\models\Task[] $allTasks */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новый публичный экзамен';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/exam/index']) ?>" class="hover:text-base-100 no-underline">Публичные экзамены</a>
    <span>/</span><span class="text-base-100">Новый</span>
</div>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 300px;">
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Задачи</h2>
            <div class="space-y-2">
                <?php foreach ($allTasks as $task): ?>
                    <div class="border rounded-lg p-3" style="border-color:#E2E8F0;">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="task_ids[]" value="<?= $task->id ?>" class="mt-0.5 accent-acid-lime shrink-0">
                            <div class="min-w-0">
                                <span class="badge-indigo"><?= $task->task_number ? 'Задание ' . $task->task_number : 'Без номера' ?></span>
                                <p class="text-sm text-base-400 truncate mt-1"><?= Html::encode(mb_substr(strip_tags($task->content), 0, 100)) ?></p>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card">
                <div class="field-group">
                    <label>Название *</label>
                    <input type="text" name="title" class="input" required autofocus>
                </div>
                <div class="field-group">
                    <label>Таймер (минут)</label>
                    <input type="number" name="duration_minutes" class="input">
                </div>
                <div class="field-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_full_scored" value="1" checked class="accent-acid-lime">
                        <span>Полный балл (0–100)</span>
                    </label>
                </div>
                <div class="field-group mb-0">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_proctored" value="1" checked class="accent-acid-lime" disabled>
                        <input type="hidden" name="is_proctored" value="1">
                        <span>Защищённый режим (обязателен для публичных)</span>
                    </label>
                </div>
            </div>
            <?= Html::submitButton('Создать', ['class' => 'btn-primary w-full py-2.5']) ?>
        </div>
    </div>
</form>