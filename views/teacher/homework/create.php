<?php
/** @var yii\web\View $this */
/** @var app\models\Group[] $groups */
/** @var app\models\Task[] $allTasks */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новое ДЗ';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/homework/index']) ?>"
       class="hover:text-base-100 no-underline">ДЗ</a>
    <span>/</span>
    <span class="text-base-100">Новое</span>
</div>

<?php if ($error): ?>
    <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
<?php endif; ?>

<form method="post" id="hw-form">
    <?= Html::hiddenInput('sessionId', $sessionId ?? null) ?>
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 300px;">
        <!-- Левая колонка — задачи -->
        <div>
            <div class="field-group">
    <label>Описание / инструкция к ДЗ (необязательно)</label>
    <textarea name="description" class="input" rows="3"
              placeholder="Например: повторить тему, обратить внимание на..."><?= Html::encode($description ?? '') ?></textarea>
</div>

<div class="field-group">
    <label>Устные вопросы / задания без автопроверки (необязательно)</label>
    <textarea name="oral_questions" class="input" rows="3"
              placeholder="Например: подготовить устный ответ на вопрос..."><?= Html::encode($oralQuestions ?? '') ?></textarea>
    <p class="text-xs text-base-400 mt-1">Ученик увидит это как часть задания, но баллы за это не начисляются автоматически.</p>
</div>
            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Задачи</h2>

                <!-- Подсказка про уже выданные -->
                <div id="assigned-hint" class="hidden alert-info mb-4 text-xs">
                    Задачи отмеченные
                    <span class="font-semibold text-acid-pink">★</span>
                    уже выдавались ученикам выбранной группы.
                </div>

                <!-- Фильтр по номеру -->
                <div class="mb-4">
                    <p class="text-xs text-base-400 mb-2">Фильтр по номеру задания:</p>
                    <div class="flex gap-1.5 flex-wrap">
                        <button type="button"
                                onclick="filterTasks('all')"
                                id="fb-all"
                                class="btn-primary text-xs py-1 px-2.5">
                            Все
                        </button>
                        <button type="button"
                                onclick="filterTasks('none')"
                                id="fb-none"
                                class="btn-secondary text-xs py-1 px-2.5">
                            Без номера
                        </button>
                        <?php for ($i = 1; $i <= 27; $i++): ?>
                            <button type="button"
                                    onclick="filterTasks(<?= $i ?>)"
                                    id="fb-<?= $i ?>"
                                    class="btn-secondary text-xs py-1 px-2.5">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Список задач -->
                <div class="space-y-2" id="task-list">
                    <?php foreach ($allTasks as $task): ?>
                        <div class="task-item rounded-lg overflow-hidden border border-base-700"
                             data-task-id="<?= $task->id ?>"
                             data-number="<?= $task->task_number ?? 'none' ?>">

                            <div class="flex items-start gap-3 p-3">
                                <input type="checkbox"
                                       name="task_ids[]"
                                       value="<?= $task->id ?>"
                                       id="task-<?= $task->id ?>"
                                       class="mt-0.5 accent-acid-lime shrink-0"
                                       onchange="onTaskToggle(<?= $task->id ?>, this.checked)">

                                <label for="task-<?= $task->id ?>"
                                       class="flex-1 cursor-pointer min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <?php if ($task->task_number): ?>
                                            <span class="badge-indigo shrink-0">
                                                Задание <?= $task->task_number ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-gray shrink-0">
                                                Без номера
                                            </span>
                                        <?php endif; ?>

                                        <!-- Пометка "уже выдавалась" — управляется JS -->
                                        <span class="assigned-mark hidden text-xs text-acid-pink shrink-0"
                                              data-task-id="<?= $task->id ?>">
                                            ★ уже выдавалась
                                        </span>

                                        <?php foreach ($task->tags as $tag): ?>
                                            <span class="badge-gray">
                                                <?= Html::encode($tag->name) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>

                                    <p class="text-sm text-base-400 truncate">
                                        <?= Html::encode(mb_substr(strip_tags($task->content), 0, 120)) ?>
                                    </p>
                                </label>

                                <!-- Поле баллов — появляется при выборе -->
                                <div id="pts-<?= $task->id ?>"
                                     class="hidden shrink-0 text-center"
                                     style="width: 72px;">
                                    <p class="text-xs text-base-400 mb-0.5">Баллов</p>
                                    <input type="number"
                                           name="max_points[<?= $task->id ?>]"
                                           value="<?= $task->difficulty * 10 ?>"
                                           min="1" max="100"
                                           class="input text-sm py-1.5 text-center px-1"
                                           oninput="updateSummary()">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div id="no-tasks-msg" class="hidden text-center py-8 text-base-400 text-sm">
                        Нет задач с таким номером.
                    </div>
                </div>
            </div>
        </div>

        <!-- Правая колонка — параметры -->
        <div class="space-y-4">

            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Параметры ДЗ</h2>

            <?php if (!empty($session)): ?>
                <div class="field-group">
                    <label>Назначено</label>
                    <div class="input flex items-center" style="background:#F8FAFC; color:#64748B;">
                        <?= $session->student
                            ? Html::encode($session->student->name)
                            : Html::encode($session->group->name ?? '—') ?>
                        <span class="text-xs text-base-400 ml-2">(из занятия «<?= Html::encode($session->title) ?>»)</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="field-group">
                    <label>Название ДЗ (необязательно)</label>
                    <input type="text" name="title" class="input"
                        placeholder="Автоматически, если не заполнено">
                </div>

                <div class="field-group">
                    <label>Группа</label>
                    <select name="group_id" class="input" id="group-select"
                            onchange="onGroupChange(this.value)">
                        <option value="">Без группы</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group->id ?>">
                                <?= Html::encode($group->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

                <div class="field-group">
                    <label>Дедлайн</label>
                    <input type="datetime-local" name="deadline_at" class="input">
                </div>
            </div>

            <!-- Итог выбора -->
            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-3">Итог</h2>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-base-400">Задач выбрано:</span>
                        <span id="summary-count" class="font-semibold text-base-100">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-base-400">Макс. баллов:</span>
                        <span id="summary-points" class="font-semibold text-acid-lime">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-base-400">Уже выдавались:</span>
                        <span id="summary-assigned" class="font-semibold text-acid-pink">—</span>
                    </div>
                </div>
            </div>

            <?= Html::submitButton('Выдать ДЗ', ['class' => 'btn-primary w-full py-3']) ?>

        </div>
    </div>
</form>

<script>
// ID задач уже выданных выбранной группе (загружается при смене группы)
let assignedIds = [];

// Загрузить пометки для группы
function onGroupChange(groupId) {
    // Сбрасываем пометки
    document.querySelectorAll('.assigned-mark').forEach(el => el.classList.add('hidden'));
    document.getElementById('assigned-hint').classList.add('hidden');
    assignedIds = [];

    if (!groupId) {
        updateSummary();
        return;
    }

    fetch('<?= Url::to(['/teacher/homework/group-task-ids']) ?>?group_id=' + groupId, {
        headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(ids => {
        assignedIds = ids;

        if (ids.length > 0) {
            document.getElementById('assigned-hint').classList.remove('hidden');
        }

        ids.forEach(id => {
            const mark = document.querySelector('.assigned-mark[data-task-id="' + id + '"]');
            if (mark) mark.classList.remove('hidden');
        });

        updateSummary();
    });
}

// Показать/скрыть поле баллов
function onTaskToggle(taskId, checked) {
    const pts = document.getElementById('pts-' + taskId);
    if (checked) {
        pts.classList.remove('hidden');
    } else {
        pts.classList.add('hidden');
    }
    updateSummary();
}

// Обновить итог
function updateSummary() {
    const checked = document.querySelectorAll('input[name="task_ids[]"]:checked');
    let total = 0;
    let assignedCount = 0;

    checked.forEach(cb => {
        const id     = parseInt(cb.value);
        const input  = document.querySelector('input[name="max_points[' + id + ']"]');
        if (input) total += parseInt(input.value) || 0;
        if (assignedIds.includes(id)) assignedCount++;
    });

    document.getElementById('summary-count').textContent  = checked.length;
    document.getElementById('summary-points').textContent = total;
    document.getElementById('summary-assigned').textContent =
        assignedCount > 0 ? assignedCount + ' шт.' : '—';
}

// Фильтр задач по номеру
function filterTasks(number) {
    let visible = 0;

    document.querySelectorAll('.task-item').forEach(el => {
        const n = el.dataset.number;
        const show = number === 'all'
            || (number === 'none' && n === 'none')
            || (number !== 'none' && parseInt(n) === parseInt(number));

        el.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('no-tasks-msg').style.display = visible === 0 ? '' : 'none';

    // Подсветка активной кнопки
    document.querySelectorAll('[id^="fb-"]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    });

    const key = number === 'all' ? 'all' : (number === 'none' ? 'none' : number);
    const active = document.getElementById('fb-' + key);
    if (active) {
        active.classList.add('btn-primary');
        active.classList.remove('btn-secondary');
    }
}
</script>