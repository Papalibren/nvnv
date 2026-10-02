<?php
/** @var app\models\Homework $homework */
/** @var array $stats */
/** @var app\models\HomeworkStudent[] $submissions */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = $homework->title;

// Разделяем по статусу
$submitted = array_filter($submissions, fn($hs) =>
    in_array($hs->status, ['submitted', 'reviewed']));
$pending = array_filter($submissions, fn($hs) =>
    in_array($hs->status, ['assigned', 'in_progress']));
?>

<div class="flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-base-100 mb-1"><?= Html::encode($homework->title) ?></h1>
        <!-- ...остальное без изменений... -->
    </div>
    <a href="<?= Url::to(['/teacher/homework/submissions', 'id' => $homework->id]) ?>" class="btn-primary">
        Проверить работы
    </a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4">
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    </div>
<?php endif; ?>

<!-- Шапка -->
<div class="card mb-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-base-100 mb-2">
                <?= Html::encode($homework->title) ?>
            </h1>
            <div class="flex items-center gap-4 text-sm text-base-400 flex-wrap">
                <?php if ($homework->group): ?>
                    <span>
                        Группа: <strong class="text-base-100"><?= Html::encode($homework->group->name) ?></strong>
                    </span>
                <?php endif; ?>
                <?php if ($homework->deadline_at): ?>
                    <span class="text-base-400">
                        Дедлайн: <?= Yii::$app->formatter->asDatetime($homework->deadline_at, 'php:d.m.Y H:i') ?>
                        <?= $homework->isOverdue() ? '(истёк)' : '' ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Статистика -->
        <div class="flex gap-5 text-center shrink-0">
            <div>
                <p class="text-2xl font-bold text-base-100"><?= $stats['total'] ?></p>
                <p class="text-xs text-base-400 mt-0.5">Всего</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-acid-lime"><?= $stats['submitted'] ?></p>
                <p class="text-xs text-base-400 mt-0.5">Сдали</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-acid-pink"><?= $stats['pending'] ?></p>
                <p class="text-xs text-base-400 mt-0.5">Не сдали</p>
            </div>
        </div>
    </div>
</div>

<?php if ($homework->description): ?>
    <div class="card mb-4">
        <p class="text-xs font-semibold text-base-400 uppercase tracking-wide mb-1.5">Описание задания</p>
        <p class="text-sm text-base-100"><?= nl2br(Html::encode($homework->description)) ?></p>
    </div>
<?php endif; ?>

<?php if ($homework->oral_questions): ?>
    <div class="card mb-4" style="border: 1px solid rgba(168,85,247,0.25); background: rgba(168,85,247,0.03);">
        <p class="text-xs font-semibold text-acid-violet uppercase tracking-wide mb-1.5">Устные вопросы к занятию</p>
        <p class="text-sm text-base-100"><?= nl2br(Html::encode($homework->oral_questions)) ?></p>
    </div>
<?php endif; ?>

<!-- Задачи в ДЗ -->
<div class="card mb-6">
    <h2 class="text-sm font-semibold text-base-100 mb-3">
        Задачи в этом ДЗ (<?= count($homework->homeworkTasks) ?>)
    </h2>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($homework->homeworkTasks as $ht): ?>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-base-900 text-sm">
                <?php if ($ht->task->task_number): ?>
                    <span class="badge-indigo">Задание <?= $ht->task->task_number ?></span>
                <?php else: ?>
                    <span class="badge-gray">Без номера</span>
                <?php endif; ?>
                <span class="text-base-100 truncate max-w-32">
                    <?= Html::encode(mb_substr(strip_tags($ht->task->content), 0, 40)) ?>
                </span>
                <span class="text-base-400 text-xs shrink-0"><?= $ht->max_points ?> б.</span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="space-y-2">
    <?php foreach ($homework->homeworkTasks as $ht): ?>
        <?php $task = $ht->task; ?>
        <a href="<?= Url::to(['tasks/<id:\d+>', 'id' => $task->id]) ?>"
           class="flex items-center justify-between p-3 rounded-lg bg-base-900 no-underline hover:bg-base-800 transition-colors duration-150">
            <div class="flex items-center gap-3 min-w-0">
                <span class="badge-indigo shrink-0">
                    <?= $task->task_number ? 'Задание ' . $task->task_number : '№' . $task->id ?>
                </span>
                <span class="text-sm text-base-100 truncate">
                    <?= Html::encode(mb_substr(strip_tags($task->content), 0, 80)) ?>…
                </span>
            </div>
            <span class="text-xs text-base-400 shrink-0 ml-3"><?= $ht->max_points ?> б.</span>
        </a>
    <?php endforeach; ?>
</div>
</div>

<!-- Табы -->
<div class="flex gap-1 mb-4" id="hw-tabs">
    <button type="button"
            onclick="showTab('submitted')"
            id="tab-submitted"
            class="btn-primary text-sm py-2 px-4">
        Сдали (<?= count($submitted) ?>)
    </button>
    <button type="button"
            onclick="showTab('pending')"
            id="tab-pending"
            class="btn-secondary text-sm py-2 px-4">
        Не сдали (<?= count($pending) ?>)
    </button>
</div>

<!-- Сдавшие -->
<div id="panel-submitted" class="space-y-4">
    <?php if (empty($submitted)): ?>
        <div class="card text-center py-8">
            <p class="text-base-400 text-sm">Никто ещё не сдал.</p>
        </div>
    <?php else: ?>
        <?php foreach ($submitted as $hs): ?>
            <?php
            // Группируем ответы по задаче, берём последнюю попытку
            $answersByTask = [];
            foreach ($hs->answers as $ans) {
                $tid = $ans->homework_task_id;
                if (!isset($answersByTask[$tid])
                    || $ans->attempt_number > $answersByTask[$tid]->attempt_number) {
                    $answersByTask[$tid] = $ans;
                }
            }
            $earnedPoints = array_sum(array_map(fn($a) => $a->points_earned ?? 0, $answersByTask));
            $correctCount = count(array_filter($answersByTask, fn($a) => $a->is_correct));
            ?>
            <div class="card">
                <!-- Ученик и итог -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="font-semibold text-base-100">
                            <?= Html::encode($hs->student->name) ?>
                        </p>
                        <p class="text-xs text-base-400 mt-0.5">
                            Сдано: <?= Yii::$app->formatter->asDatetime($hs->submitted_at, 'php:d.m.Y H:i') ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-center">
                        <div>
                            <p class="text-lg font-bold text-acid-lime"><?= $earnedPoints ?></p>
                            <p class="text-xs text-base-400">баллов</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-base-100">
                                <?= $correctCount ?>/<?= count($homework->homeworkTasks) ?>
                            </p>
                            <p class="text-xs text-base-400">верно</p>
                        </div>
                        <span class="badge-indigo">Сдано</span>
                    </div>
                </div>

                <!-- Ответы по задачам -->
                <div class="space-y-2">
                    <?php foreach ($homework->homeworkTasks as $ht): ?>
                        <?php $ans = $answersByTask[$ht->id] ?? null; ?>
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-base-900 text-sm">

                            <!-- Номер задачи -->
                            <div class="shrink-0 w-24">
                                <?php if ($ht->task->task_number): ?>
                                    <span class="badge-indigo">
                                        Зад. <?= $ht->task->task_number ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge-gray">Без №</span>
                                <?php endif; ?>
                            </div>

                            <!-- Ответ ученика -->
                            <div class="flex-1 min-w-0">
                                <?php if ($ans && $ans->answer_text): ?>
                                    <span class="font-mono text-base-100">
                                        <?= Html::encode($ans->answer_text) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-base-400 italic">нет ответа</span>
                                <?php endif; ?>

                                <!-- Файл ученика -->
                                <?php if ($ans && $ans->file_path): ?>
                                    <a href="/files/<?= $ans->file_path ?>"
                                       class="ml-3 inline-flex items-center gap-1 text-xs
                                              text-acid-cyan hover:text-acid-lime no-underline"
                                       target="_blank">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                             stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32"/>
                                        </svg>
                                        Файл решения
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Правильный ответ -->
                            <div class="shrink-0 text-right">
                                <p class="text-xs text-base-400 mb-0.5">Правильно:</p>
                                <code class="text-xs font-mono text-base-100">
                                    <?= Html::encode($ht->task->answer) ?>
                                </code>
                            </div>

                            <!-- Результат -->
                            <div class="shrink-0 text-center w-16">
                                <?php if ($ans): ?>
                                    <span class="text-lg <?= $ans->is_correct ? 'text-acid-lime' : 'text-acid-pink' ?>">
                                        <?= $ans->is_correct ? '✓' : '✗' ?>
                                    </span>
                                    <p class="text-xs text-base-400">
                                        +<?= $ans->points_earned ?? 0 ?>б
                                    </p>
                                    <?php if ($ans->attempt_number === 2): ?>
                                        <p class="text-xs text-acid-cyan">2-я попытка</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-base-400 text-sm">—</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Не сдавшие -->
<div id="panel-pending" class="space-y-3 hidden">
    <?php if (empty($pending)): ?>
        <div class="card text-center py-8">
            <p class="text-base-400 text-sm">Все сдали!</p>
        </div>
    <?php else: ?>
        <?php foreach ($pending as $hs): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-white
                        border border-base-700">
                <div>
                    <p class="font-medium text-base-100">
                        <?= Html::encode($hs->student->name) ?>
                    </p>
                    <p class="text-xs text-base-400 mt-0.5">
                        <?= \app\models\HomeworkStudent::getLabel($hs->status) ?>
                    </p>
                </div>
                <?php if ($homework->deadline_at && $homework->isOverdue()): ?>
                    <span class="badge-pink">Просрочено</span>
                <?php else: ?>
                    <span class="badge-gray">Ожидаем</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function showTab(name) {
    ['submitted', 'pending'].forEach(tab => {
        document.getElementById('panel-' + tab).classList.add('hidden');
        document.getElementById('tab-' + tab).classList.remove('btn-primary');
        document.getElementById('tab-' + tab).classList.add('btn-secondary');
    });

    document.getElementById('panel-' + name).classList.remove('hidden');
    document.getElementById('tab-' + name).classList.add('btn-primary');
    document.getElementById('tab-' + name).classList.remove('btn-secondary');
}
</script>