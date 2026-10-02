<?php
/** @var app\models\Task[] $allTasks */
/** @var int[] $assignedTaskIds */
use yii\helpers\Html;
?>

<?php if (!empty($assignedTaskIds)): ?>
    <div class="alert-info mb-4 text-xs">
        Задачи отмеченные <span class="text-acid-pink font-semibold">✓ уже выдавались</span>
        ученикам этой группы — рекомендуется не повторять.
    </div>
<?php endif; ?>

<div class="flex gap-2 flex-wrap mb-4">
    <button type="button" onclick="filterTasks(0)"
            class="btn-primary text-xs py-1 px-2" id="filter-all">Все</button>
    <?php for ($i = 1; $i <= 27; $i++): ?>
        <button type="button" onclick="filterTasks(<?= $i ?>)"
                class="btn-secondary text-xs py-1 px-2" id="filter-<?= $i ?>">
            <?= $i ?>
        </button>
    <?php endfor; ?>
</div>

<div class="space-y-2" id="task-list">
    <?php foreach ($allTasks as $task): ?>
        <?php $alreadyAssigned = in_array($task->id, $assignedTaskIds); ?>
        <div class="task-item border rounded-lg overflow-hidden"
             data-number="<?= $task->task_number ?? 0 ?>"
             style="border-color: #E2E8F0;">
            <div class="flex items-start gap-3 p-3">
                <input type="checkbox"
                       name="task_ids[]"
                       value="<?= $task->id ?>"
                       id="task-<?= $task->id ?>"
                       class="mt-1 accent-acid-lime shrink-0"
                       onchange="togglePoints(<?= $task->id ?>, this.checked)">
                <label for="task-<?= $task->id ?>" class="flex-1 cursor-pointer">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <?php if ($task->task_number): ?>
                            <span class="badge-indigo">Задание <?= $task->task_number ?></span>
                        <?php else: ?>
                            <span class="badge-gray">Без номера</span>
                        <?php endif; ?>
                        <?php if ($alreadyAssigned): ?>
                            <span class="text-xs text-acid-pink">✓ уже выдавалась</span>
                        <?php endif; ?>
                        <?php foreach ($task->tags as $tag): ?>
                            <span class="badge-gray"><?= Html::encode($tag->name) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-base-400 line-clamp-2">
                        <?= Html::encode(mb_substr(strip_tags($task->content), 0, 100)) ?>…
                    </p>
                </label>
                <div id="points-<?= $task->id ?>" class="hidden shrink-0">
                    <label class="text-xs text-base-400 mb-0.5 block">Баллов</label>
                    <input type="number"
                           name="max_points[<?= $task->id ?>]"
                           value="<?= $task->difficulty * 10 ?>"
                           min="1" max="100"
                           class="input text-sm py-1 text-center"
                           style="width: 70px;"
                           oninput="updateTotals()">
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>