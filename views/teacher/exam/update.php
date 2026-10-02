<?php
/** @var app\models\Exam $exam */
/** @var app\models\Group[] $groups */
/** @var app\models\Task[] $allTasks */
/** @var int[] $selectedTaskIds */
/** @var bool $hasAttempts */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Изменить: ' . $exam->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/exam/index']) ?>" class="hover:text-base-100 no-underline">Экзамены</a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($exam->title) ?></span>
</div>

<?php if ($error): ?>
    <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
<?php endif; ?>

<?php if ($hasAttempts): ?>
    <div class="alert-info mb-4">
        Уже есть попытки прохождения — состав задач менять нельзя, доступны только название, группа и таймер.
    </div>
<?php endif; ?>

<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 300px;">

        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Задачи</h2>

            <?php if ($hasAttempts): ?>
                <div class="space-y-2 opacity-50 pointer-events-none">
                    <?php foreach ($exam->examTasks as $et): ?>
                        <div class="border rounded-lg p-3" style="border-color:#E2E8F0;">
                            <span class="badge-indigo">
                                <?= $et->task->task_number ? 'Задание ' . $et->task->task_number : 'Без номера' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($allTasks as $task): ?>
                        <div class="border rounded-lg p-3" style="border-color:#E2E8F0;">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="task_ids[]" value="<?= $task->id ?>"
                                       class="mt-0.5 accent-acid-lime shrink-0"
                                       <?= in_array($task->id, $selectedTaskIds) ? 'checked' : '' ?>>
                                <div class="min-w-0">
                                    <span class="badge-indigo">
                                        <?= $task->task_number ? 'Задание ' . $task->task_number : 'Без номера' ?>
                                    </span>
                                    <p class="text-sm text-base-400 truncate mt-1">
                                        <?= Html::encode(mb_substr(strip_tags($task->content), 0, 100)) ?>
                                    </p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-4">Параметры</h2>

                <div class="field-group">
                    <label>Название *</label>
                    <input type="text" name="title" class="input" value="<?= Html::encode($exam->title) ?>" required>
                </div>

                <div class="field-group">
                    <label>Группа</label>
                    <select name="group_id" class="input">
                        <option value="">Без группы</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group->id ?>" <?= $exam->group_id == $group->id ? 'selected' : '' ?>>
                                <?= Html::encode($group->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label>Таймер (минут)</label>
                    <input type="number" name="duration_minutes" class="input"
                           value="<?= $exam->duration_minutes ?? '' ?>">
                </div>

                <div class="field-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_full_scored" value="1" class="accent-acid-lime"
                               <?= $exam->is_full_scored ? 'checked' : '' ?>>
                        <span>Полный балл (0–100)</span>
                    </label>
                </div>

                <div class="field-group mb-0">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_proctored" value="1" class="accent-acid-lime"
                               <?= $exam->is_proctored ? 'checked' : '' ?>>
                        <span>Защищённый режим</span>
                    </label>
                </div>
            </div>

            <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full py-2.5']) ?>
        </div>
    </div>
</form>