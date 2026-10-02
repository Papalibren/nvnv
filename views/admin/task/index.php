<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Task;
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Задачи</h1>
        <p class="text-sm text-base-400 mt-0.5">
            Всего: <?= $dataProvider->totalCount ?>
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= Url::to(['/admin/task/tags']) ?>" class="btn-secondary">
            Управление тегами
        </a>
        <a href="<?= Url::to(['/admin/task/create']) ?>" class="btn-primary">
            + Новая задача
        </a>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-6">
    <form hx-get="<?= Url::to(['/admin/task/index']) ?>"
          hx-target="#task-list"
          hx-trigger="change"
          class="flex gap-4 flex-wrap items-end">

        <div>
            <label class="mb-1">Номер задания</label>
            <select name="number" class="input" style="width: 160px;">
                <option value="">Все</option>
                <?php for ($i = 1; $i <= 27; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="mb-1">Статус</label>
            <select name="status" class="input" style="width: 160px;">
                <option value="">Все</option>
                <option value="published">Опубликована</option>
                <option value="draft">Черновик</option>
            </select>
        </div>

        <div>
            <label class="mb-1">Сложность</label>
            <select name="difficulty" class="input" style="width: 160px;">
                <option value="">Любая</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

    </form>
</div>

<!-- Список -->
<div id="task-list">
    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>