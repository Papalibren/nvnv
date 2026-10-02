<?php
/** @var app\models\Task $task */
/** @var string $mode */
/** @var bool $showAnswer */
/** @var bool $showSolution */
use yii\helpers\Html;
use app\helpers\ContentRenderer;

$colorMap = [
    'lime'   => '#22C55E',
    'cyan'   => '#3B82F6',
    'pink'   => '#EF4444',
];

$diffColor = match(true) {
    $task->difficulty <= 3 => $colorMap['lime'],
    $task->difficulty <= 7 => $colorMap['cyan'],
    default                => $colorMap['pink'],
};

$images = array_filter($task->files, fn($f) => $f->isImage());
$attachments = array_filter($task->files, fn($f) => !$f->isImage());
?>

<?php $accentClass = 'task-accent-' . ((($task->task_number ?? $task->id) % 4) + 1); ?>
<article class="card <?= $accentClass ?>" data-task-id="<?= $task->id ?>">

<!-- Заголовок -->
<header class="flex items-start justify-between gap-4 mb-4 flex-wrap">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="/tasks/<?= $task->id ?>"
           class="badge-indigo no-underline hover:brightness-110 transition-all duration-150">
            Задание <?= $task->task_number ?>
        </a>
        <span class="text-xs text-base-400 font-mono">#<?= $task->id ?></span>
        <?php foreach ($task->tags as $tag): ?>
            <span class="badge-gray"><?= Html::encode($tag->name) ?></span>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-0.5 items-center shrink-0">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <div class="w-1.5 h-3 rounded-sm"
                 style="background: <?= $i <= $task->difficulty ? $diffColor : '#E2E8F0' ?>"></div>
        <?php endfor; ?>
        <span class="text-base-400 text-xs ml-1"><?= $task->difficulty ?>/10</span>
    </div>
</header>

    <!-- Заголовок задачи (если есть) -->
    <?php if ($task->title): ?>
        <h3 class="text-base font-semibold text-base-100 mb-2">
            <?= Html::encode($task->title) ?>
        </h3>
    <?php endif; ?>

    <!-- Условие -->
    <div class="prose-task text-base text-base-100 leading-relaxed mb-4">
        <?= ContentRenderer::render($task->content) ?>
    </div>

    <!-- Картинки -->
    <?php if ($images): ?>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <?php foreach ($images as $img): ?>
                <a href="/files/<?= $img->path ?>" target="_blank">
                    <img src="/files/<?= $img->path ?>"
                         alt="<?= Html::encode($img->filename) ?>"
                         class="rounded-lg border border-base-700 w-full object-contain max-h-64">
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Файлы-приложения -->
    <?php if ($attachments): ?>
        <div class="flex flex-wrap gap-2 mb-4">
            <?php foreach ($attachments as $file): ?>
                <a href="/files/<?= $file->path ?>"
                   class="btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-2 no-underline"
                   target="_blank">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32"/>
                    </svg>
                    <?= Html::encode($file->filename) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Ответ под спойлером -->
    <?php if ($showAnswer && !$task->isManualAnswer()): ?>
        <div class="task-disclosure">
            <button type="button" class="task-disclosure-trigger"
                    data-show="Показать ответ" data-hide="Скрыть ответ"
                    onclick="toggleDisclosure(this)">
                Показать ответ
            </button>
            <div class="task-disclosure-panel">
                <div class="task-disclosure-inner">
                    <code class="text-acid-lime font-mono text-sm"><?= Html::encode($task->answer) ?></code>
                </div>
            </div>
        </div>
    <?php elseif ($showAnswer && $task->isManualAnswer()): ?>
        <p class="text-xs text-base-400 mt-3">
            У этой задачи нет единственного короткого ответа — решение смотрите в разборе ниже.
        </p>
    <?php endif; ?>

    <!-- Разбор -->
    <?php if ($showSolution && $task->solution_content): ?>
        <div class="task-disclosure">
            <button type="button" class="task-disclosure-trigger"
                    data-show="Полный разбор" data-hide="Скрыть разбор"
                    onclick="toggleDisclosure(this)">
                Полный разбор
            </button>
            <div class="task-disclosure-panel">
                <div class="task-disclosure-inner prose-task text-sm">
                    <?= ContentRenderer::render($task->solution_content) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

<!-- Ссылка на учебник -->
<?php if ($task->bookPages): ?>
<div class="mt-4 pt-4" style="border-top: 1px solid #E2E8F0;">
    <p class="text-base-400 text-xs mb-2">Теория по теме:</p>
    <div class="flex gap-3 flex-wrap">
        <?php foreach ($task->bookPages as $page): ?>
            <?php $section = $page->chapter->section ?? null; ?>
            <?php if ($section): ?>
                <a href="<?= $page->getUrl() ?>"
                   class="text-acid-lime text-sm hover:text-acid-violet no-underline">
                    → <?= Html::encode($page->title) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Ссылка на отдельную страницу -->
    <?php if ($showOpenLink): ?>
        <div class="mt-4 pt-4" style="border-top: 1px solid #E2E8F0;">
            <a href="/tasks/<?= $task->id ?>"
               class="text-sm text-acid-lime hover:text-acid-violet no-underline inline-flex items-center gap-1">
                Открыть отдельно
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </a>
        </div>
    <?php endif; ?>

</article>
