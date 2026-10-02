<?php
/** @var yii\web\View $this */
/** @var app\models\TaskTag $tag */
use yii\helpers\Html;
use yii\helpers\Url;

$taskCount = $tag->getTaskCount();
?>

<div id="tag-row-<?= $tag->id ?>"
     class="flex items-center justify-between gap-3 p-3 rounded-lg bg-base-900">

    <!-- Режим просмотра -->
    <div class="flex items-center gap-3 flex-1 min-w-0" id="tag-view-<?= $tag->id ?>">
        <span class="font-medium text-base-100 truncate"><?= Html::encode($tag->name) ?></span>
        <span class="text-xs text-base-400 shrink-0"><?= $tag->slug ?></span>
        <?php if ($taskCount > 0): ?>
            <span class="badge-indigo shrink-0"><?= $taskCount ?> задач</span>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <!-- Кнопка редактирования — превращает блок в форму -->
        <button type="button"
                class="btn-ghost text-xs"
                onclick="document.getElementById('tag-view-<?= $tag->id ?>').classList.add('hidden');
                         document.getElementById('tag-edit-<?= $tag->id ?>').classList.remove('hidden');">
            Изменить
        </button>

        <!-- Кнопка удаления -->
        <button type="button"
                class="btn-ghost text-xs text-acid-pink"
                hx-delete="<?= Url::to(['/admin/task/tag-delete', 'id' => $tag->id]) ?>"
                hx-target="#tag-row-<?= $tag->id ?>"
                hx-swap="outerHTML swap:300ms"
                hx-confirm="<?= $taskCount > 0
                    ? "Тег используется в {$taskCount} задачах. Удалить всё равно?"
                    : 'Удалить тег?' ?>">
            Удалить
        </button>
    </div>

    <!-- Режим редактирования (скрыт по умолчанию) -->
    <form id="tag-edit-<?= $tag->id ?>"
          class="hidden flex items-center gap-2 flex-1"
          hx-post="<?= Url::to(['/admin/task/tag-update', 'id' => $tag->id]) ?>"
          hx-target="#tag-row-<?= $tag->id ?>"
          hx-swap="outerHTML">
        <input type="text" name="name" value="<?= Html::encode($tag->name) ?>"
               class="input py-1.5 text-sm flex-1" required autofocus>
        <button type="submit" class="btn-primary text-xs py-1.5 px-3">Сохранить</button>
        <button type="button" class="btn-ghost text-xs"
                onclick="document.getElementById('tag-edit-<?= $tag->id ?>').classList.add('hidden');
                         document.getElementById('tag-view-<?= $tag->id ?>').classList.remove('hidden');">
            Отмена
        </button>
    </form>

</div>