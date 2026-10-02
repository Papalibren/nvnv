<?php

/** @var app\models\Group[] $groups */
/** @var app\models\Task[] $allTasks */
/** @var string|null $error */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новый экзамен';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/exam/index']) ?>" class="hover:text-base-100 no-underline">Экзамены</a>
    <span>/</span>
    <span class="text-base-100">Новый</span>
</div>

<?php if ($error): ?>
    <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
<?php endif; ?>

<form method="post">
    <?= Html::hiddenInput('sessionId', $sessionId ?? null) ?>
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 300px;">

        <!-- Задачи -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Выберите задачи (произвольно)</h2>

            <div class="flex gap-1.5 flex-wrap mb-4">
                <button type="button" onclick="filterTasks('all')" id="fb-all" class="btn-primary text-xs py-1 px-2.5">Все</button>
                <button type="button" onclick="filterTasks('none')" id="fb-none" class="btn-secondary text-xs py-1 px-2.5">Без номера</button>
                <?php for ($i = 1; $i <= 27; $i++): ?>
                    <button type="button" onclick="filterTasks(<?= $i ?>)" id="fb-<?= $i ?>" class="btn-secondary text-xs py-1 px-2.5"><?= $i ?></button>
                <?php endfor; ?>
            </div>

            <div class="space-y-2" id="task-list">
                <?php foreach ($allTasks as $task): ?>
                    <div class="task-item border rounded-lg p-3" data-number="<?= $task->task_number ?? 'none' ?>" style="border-color:#E2E8F0;">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="task_ids[]" value="<?= $task->id ?>"
                                class="mt-0.5 accent-acid-lime shrink-0" onchange="updateCount()">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <?php if ($task->task_number): ?>
                                        <span class="badge-indigo">Задание <?= $task->task_number ?></span>
                                    <?php else: ?>
                                        <span class="badge-gray">Без номера</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-base-400 truncate">
                                    <?= Html::encode(mb_substr(strip_tags($task->content), 0, 100)) ?>
                                </p>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Параметры -->
        <div class="space-y-4">

            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-4">Параметры</h2>

                <div class="field-group">
                    <label>Название *</label>
                    <input type="text" name="title" class="input" required autofocus>
                </div>

                <div class="field-group">
                    <label>Группа</label>
                    <select name="group_id" class="input">
                        <option value="">Без группы</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group->id ?>"><?= Html::encode($group->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label>Таймер (минут, пусто — без таймера)</label>
                    <input type="number" name="duration_minutes" class="input" placeholder="235">
                </div>

                <div class="field-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_full_scored" value="1" checked class="accent-acid-lime">
                        <span>Полный балл (0–100)</span>
                    </label>
                </div>

                <div class="field-group mb-0">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_proctored" value="1" class="accent-acid-lime">
                        <span>Защищённый режим</span>
                    </label>
                    <p class="text-xs text-base-400 mt-1.5">
                        Полноэкранный режим обязателен, фиксируется потеря фокуса.
                        Только такие экзамены дают баллы в публичный рейтинг.
                    </p>
                </div>
            </div>

            <div class="card">
                <p class="text-sm text-base-400">
                    Выбрано задач: <span id="task-count" class="font-semibold text-base-100">0</span>
                </p>
            </div>

            <?= Html::submitButton('Создать экзамен', ['class' => 'btn-primary w-full py-3']) ?>
        </div>
    </div>
</form>

<script>
    function updateCount() {
        document.getElementById('task-count').textContent =
            document.querySelectorAll('input[name="task_ids[]"]:checked').length;
    }

    function filterTasks(number) {
        document.querySelectorAll('.task-item').forEach(el => {
            const n = el.dataset.number;
            const show = number === 'all' ||
                (number === 'none' && n === 'none') ||
                (number !== 'none' && parseInt(n) === parseInt(number));
            el.style.display = show ? '' : 'none';
        });

        document.querySelectorAll('[id^="fb-"]').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        });
        const key = number === 'all' ? 'all' : (number === 'none' ? 'none' : number);
        document.getElementById('fb-' + key).classList.add('btn-primary');
        document.getElementById('fb-' + key).classList.remove('btn-secondary');
    }
</script>