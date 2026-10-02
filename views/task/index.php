<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\TaskTag[] $allTags */
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\KatexAsset;

KatexAsset::register($this);

$currentNumber     = Yii::$app->request->get('number');
$currentDifficulty = Yii::$app->request->get('difficulty');
$currentTag        = Yii::$app->request->get('tag');
?>

<div class="max-w-6xl mx-auto px-4 py-6">

    <!-- Компактная шапка в одну строку -->
    <div class="flex items-center justify-between mb-5">
        <h1 class="text-lg font-semibold text-base-100">Задачи ЕГЭ по информатике</h1>
        <span class="text-sm text-base-400"><?= $dataProvider->totalCount ?> заданий</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start">

        <!-- Сайдбар фильтров: на мобильном обычный поток, на десктопе sticky с внутренней прокруткой -->
<aside class="w-full lg:w-72 shrink-0 space-y-4 lg:sticky lg:top-20 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto lg:pb-4">

    <div class="card divide-y divide-base-700">

        <!-- Номер задания -->
        <div class="pb-4">
            <label class="mb-2">Номер задания</label>
            <div class="flex flex-wrap gap-1.5" id="number-pills">
                <button type="button" class="task-pill task-pill-wide" data-number="" onclick="TaskFilters.selectNumber(null)">Все</button>
                <?php for ($i = 1; $i <= 27; $i++): ?>
                    <button type="button" class="task-pill" data-number="<?= $i ?>" onclick="TaskFilters.selectNumber(<?= $i ?>)">
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>
        <!-- Теги -->
        <?php if ($allTags): ?>
        <div class="py-3">
            <label class="mb-2">Теги</label>
            <div class="flex flex-wrap gap-1.5" id="tag-pills">
                <?php foreach ($allTags as $tag): ?>
                    <button type="button"
                            class="task-tag-pill"
                            data-tag="<?= Html::encode($tag->slug) ?>"
                            onclick="TaskFilters.selectTag('<?= Html::encode($tag->slug) ?>')">
                        <?= Html::encode($tag->name) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
<!-- Сложность — цветовая группировка лёгкий/средний/сложный -->
<div class="pb-4">
    <div class="flex items-center justify-between mb-2">
        <label class="mb-0">Сложность</label>
        <button type="button" onclick="TaskFilters.selectDifficulty(null)"
                class="text-xs text-base-400 hover:text-acid-lime" id="difficulty-reset">Любая</button>
    </div>
    <div class="flex gap-1" id="difficulty-pills">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <?php
            $colorClass = $i <= 3 ? 'difficulty-pill-easy' : ($i <= 7 ? 'difficulty-pill-medium' : 'difficulty-pill-hard');
            ?>
            <button type="button" class="difficulty-pill <?= $colorClass ?>" data-difficulty="<?= $i ?>"
                    onclick="TaskFilters.selectDifficulty(<?= $i ?>)" title="Сложность <?= $i ?>/10">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>
    <div class="flex justify-between text-xs text-base-400 mt-1.5">
        <span>Лёгкие</span>
        <span>Средние</span>
        <span>Сложные</span>
    </div>
</div>
    </div>

</aside>

        <!-- Список задач -->
        <div class="flex-1 min-w-0">
            <div id="task-list" class="space-y-4">
                <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
            </div>
        </div>

    </div>
</div>

<script>
const TaskFilters = (function () {
    let state = {
        number: <?= $currentNumber ? (int) $currentNumber : 'null' ?>,
        difficulty: <?= $currentDifficulty ? (int) $currentDifficulty : 'null' ?>,
        tag: <?= $currentTag ? json_encode($currentTag) : 'null' ?>,
    };

    function updatePillsUI() {
        document.querySelectorAll('#number-pills .task-pill').forEach(btn => {
            const val = btn.dataset.number;
            const isActive = (val === '' && state.number === null) || (val !== '' && Number(val) === state.number);
            btn.classList.toggle('task-pill-active', isActive);
        });

        document.querySelectorAll('#difficulty-pills button').forEach(btn => {
            const val = btn.dataset.difficulty;
            const isActive = (val === '' && state.difficulty === null) || (val !== '' && Number(val) === state.difficulty);
            btn.classList.toggle('task-pill-active', isActive);
        });

        document.querySelectorAll('#tag-pills .task-tag-pill').forEach(btn => {
            const isActive = btn.dataset.tag === state.tag;
            btn.classList.toggle('task-tag-pill-active', isActive);
        });
    }

    function buildQuery() {
        const params = new URLSearchParams();
        if (state.number !== null)     params.set('number', state.number);
        if (state.difficulty !== null) params.set('difficulty', state.difficulty);
        if (state.tag !== null)        params.set('tag', state.tag);
        const qs = params.toString();
        return qs ? ('?' + qs) : '';
    }

    function fetchTasks() {
        const url = '/tasks' + buildQuery();
        history.pushState(null, '', url);
        htmx.ajax('GET', url, {
            target: '#task-list',
            swap: 'innerHTML',
            headers: { 'HX-Request': 'true' },
        });
    }

    function selectNumber(n) { state.number = n; updatePillsUI(); fetchTasks(); }
    function selectDifficulty(n) { state.difficulty = n; updatePillsUI(); fetchTasks(); }
    function selectTag(slug) { state.tag = (state.tag === slug) ? null : slug; updatePillsUI(); fetchTasks(); }

    updatePillsUI();

    return { selectNumber, selectDifficulty, selectTag };
})();
</script>