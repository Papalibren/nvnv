<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Task;

$tasks = $dataProvider->getModels();
?>

<?php if (empty($tasks)): ?>
    <div class="card text-center py-12">
        <p class="text-base-400">Задачи не найдены</p>
        <a href="<?= Url::to(['/admin/task/create']) ?>"
           class="btn-primary mt-4 inline-flex">
            Создать первую задачу
        </a>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th style="width:60px">ID</th>
                    <th style="width:90px">№ зад.</th>
                    <th>Задача</th>
                    <th style="width:200px">Теги</th>
                    <th style="width:120px">Статус</th>
                    <th style="width:110px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr id="task-row-<?= $task->id ?>">
                    <td class="text-base-400 font-mono text-xs">#<?= $task->id ?></td>
                    <td>
                        <?php if ($task->task_number): ?>
                            <span class="badge-indigo"><?= $task->task_number ?></span>
                        <?php else: ?>
                            <span class="badge-gray">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/admin/task/update', 'id' => $task->id]) ?>"
                           class="text-sm font-medium text-base-100 hover:text-acid-lime no-underline">
                            <?= $task->title
                                ? Html::encode($task->title)
                                : 'Задача #' . $task->id ?>
                        </a>
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($task->tags as $tag): ?>
                                <span class="badge-gray"><?= Html::encode($tag->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <?= $this->render('_status_badge', ['task' => $task]) ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="<?= Url::to(['/admin/task/update', 'id' => $task->id]) ?>"
                               class="btn-ghost text-xs">
                                Изменить
                            </a>
                            <button class="btn-ghost text-xs text-acid-pink"
                                    hx-delete="<?= Url::to(['/admin/task/delete', 'id' => $task->id]) ?>"
                                    hx-target="#task-row-<?= $task->id ?>"
                                    hx-swap="outerHTML swap:300ms"
                                    hx-confirm="Удалить задачу?"
                                    hx-on::response-error="alert(event.detail.xhr.responseText)">
                                Удалить
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($dataProvider->pagination->pageCount > 1): ?>
        <div class="mt-4 flex justify-center">
            <?= \yii\widgets\LinkPager::widget([
                'pagination'  => $dataProvider->pagination,
                'options'     => ['class' => 'flex gap-1'],
                'linkOptions' => ['class' => 'btn-secondary text-sm py-1.5 px-3'],
                'activePageCssClass' => 'btn-primary text-sm py-1.5 px-3',
                'disabledPageCssClass' => 'opacity-40 cursor-not-allowed',
            ]) ?>
        </div>
    <?php endif; ?>
<?php endif; ?>