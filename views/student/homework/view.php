<?php
/** @var app\models\HomeworkStudent $hs */
/** @var app\models\HomeworkTask[] $tasks */
/** @var app\models\HomeworkAnswer[] $answers */
/** @var bool $readonly */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;
use app\widgets\TaskWidget;

KatexAsset::register($this);
$this->title = $hs->homework->title;
$hw = $hs->homework;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/student/homework/index']) ?>"
       class="hover:text-base-100 no-underline">ДЗ</a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($hw->title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert-error mb-4"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
<?php endif; ?>

<!-- Шапка -->
<div class="card mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-base-100"><?= Html::encode($hw->title) ?></h1>
            <?php if ($hw->deadline_at): ?>
                <?php
                // Красным — только если ДЗ ещё не сдано и срок истёк.
                // Если уже сдано — просто нейтральная информация, без пугающего предупреждения.
                $showOverdueWarning = !$readonly && $hw->isOverdue();
                ?>
                <p class="text-sm <?= $showOverdueWarning ? 'text-acid-pink' : 'text-base-400' ?> mt-1">
                    Дедлайн: <?= Yii::$app->formatter->asDatetime($hw->deadline_at, 'php:d.m.Y H:i') ?>
                    <?php if ($showOverdueWarning): ?>
                        — <strong>просрочено</strong>, баллы × 0.5
                    <?php elseif ($readonly && $hs->wasSubmittedLate()): ?>
                        — сдано после дедлайна
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Статус автосохранения -->
        <div id="save-status" class="text-xs text-base-400"></div>
    </div>
</div>

<?php if ($hw->description): ?>
    <div class="card mb-4">
        <p class="text-xs font-semibold text-base-400 uppercase tracking-wide mb-1.5">Описание задания</p>
        <p class="text-sm text-base-100"><?= nl2br(Html::encode($hw->description)) ?></p>
    </div>
<?php endif; ?>

<?php if ($hw->oral_questions): ?>
    <div class="card mb-4" style="border: 1px solid rgba(168,85,247,0.25); background: rgba(168,85,247,0.03);">
        <p class="text-xs font-semibold text-acid-violet uppercase tracking-wide mb-1.5">Устные вопросы к занятию</p>
        <p class="text-sm text-base-100"><?= nl2br(Html::encode($hw->oral_questions)) ?></p>
    </div>
<?php endif; ?>

<!-- Задачи -->
<div class="space-y-6">
    <?php foreach ($tasks as $index => $ht): ?>
        <?php
        $answer    = $answers[$ht->id] ?? null;
        $answered  = $answer && $answer->answer_text !== '';
        ?>
        <div class="card" id="task-block-<?= $ht->id ?>">

            <!-- Номер и баллы -->
            <div class="flex items-center justify-between mb-4">
                <span class="badge-indigo">Задача <?= $index + 1 ?></span>
                <span class="text-xs text-base-400"><?= $ht->max_points ?> баллов</span>
            </div>

            <!-- Контент задачи -->
            <div class="prose-task text-sm mb-4">
                <?= ContentRenderer::render($ht->task->content) ?>
            </div>

            <!-- Картинки -->
            <?php
            $images = array_filter($ht->task->files ?? [], fn($f) => $f->isImage());
            $attachments = array_filter($ht->task->files ?? [], fn($f) => !$f->isImage());
            ?>

            <?php if ($images): ?>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <?php foreach ($images as $img): ?>
                        <img src="/files/<?= $img->path ?>" alt=""
                             class="rounded-lg border border-base-700 w-full object-contain max-h-48">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($attachments): ?>
                <div class="flex gap-2 mb-4">
                    <?php foreach ($attachments as $file): ?>
                        <a href="/files/<?= $file->path ?>"
                           class="btn-secondary text-xs py-1 px-3 no-underline"
                           target="_blank">
                            ↓ <?= Html::encode($file->filename) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Поле ответа -->
            <?php if ($readonly): ?>
                <div class="p-3 rounded-lg bg-base-900 font-mono text-sm text-base-100">
                    <?= $answer ? Html::encode($answer->answer_text) : '—' ?>
                </div>
                <?php if ($answer && $answer->file_path): ?>
                    <a href="/files/<?= $answer->file_path ?>"
                    class="btn-secondary text-xs py-1.5 px-3 mt-2 inline-flex items-center gap-2 no-underline"
                    target="_blank">
                        ↓ Прикреплённый файл
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <div class="space-y-2">
                    <input type="text"
                        id="answer-<?= $ht->id ?>"
                        value="<?= $answer ? Html::encode($answer->answer_text) : '' ?>"
                        placeholder="Введите ответ..."
                        class="input font-mono"
                        hx-post="<?= Url::to(['/student/homework/save-draft', 'id' => $hs->id]) ?>"
                        hx-trigger="keyup changed delay:800ms"
                        hx-target="#save-status"
                        hx-swap="innerHTML"
                        hx-vals='{"homework_task_id": <?= $ht->id ?>}'
                        hx-include="this"
                        name="answer">

                    <!-- Загрузка файла решения -->
                    <div class="flex items-center gap-3">
                        <label class="btn-secondary text-xs py-1.5 px-3 cursor-pointer">
                            📎 Прикрепить файл
                            <input type="file"
                                class="hidden"
                                onchange="uploadHomeworkFile(this, <?= $ht->id ?>, <?= $hs->id ?>)">
                        </label>
                        <span id="file-status-<?= $ht->id ?>" class="text-xs text-base-400"></span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>

<!-- Кнопка сдачи -->
<?php if (!$readonly): ?>
    <div class="mt-8 flex justify-end">
        <form method="post" action="<?= Url::to(['/student/homework/submit', 'id' => $hs->id]) ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <?= Html::submitButton('Сдать домашнее задание', [
                'class'   => 'btn-primary px-8 py-3',
                'onclick' => 'return confirm("Сдать ДЗ? После отправки редактирование будет ограничено.")',
            ]) ?>
        </form>
    </div>
<?php endif; ?>

<script>
// Показываем галочку после автосохранения
document.addEventListener('htmx:afterRequest', (e) => {
    const input = e.detail.elt;
    if (!input.name || input.name !== 'answer') return;

    const taskId = input.getAttribute('hx-vals')
        ? JSON.parse(input.getAttribute('hx-vals')).homework_task_id
        : null;

    if (taskId) {
        const saved = document.getElementById('saved-' + taskId);
        if (saved) {
            saved.style.opacity = '1';
            setTimeout(() => saved.style.opacity = '0', 2000);
        }
    }

    document.getElementById('save-status').textContent = 'Черновик сохранён';
    setTimeout(() => {
        const el = document.getElementById('save-status');
        if (el) el.textContent = '';
    }, 2000);
});

function uploadHomeworkFile(input, taskId, hsId) {
    if (!input.files || !input.files[0]) return;

    const file       = input.files[0];
    const status     = document.getElementById('file-status-' + taskId);
    const formData   = new FormData();

    formData.append('file', file);
    formData.append('homework_task_id', taskId);
    formData.append('<?= Yii::$app->request->csrfParam ?>',
        document.querySelector('meta[name="csrf-token"]').content);

    status.textContent = 'Загрузка...';

    fetch('<?= Url::to(['/student/homework/upload-file', 'id' => $hs->id]) ?>', {
        method: 'POST',
        body:   formData,
    })
    .then(r => r.json())
    .then(data => {
        status.textContent = data.ok ? '✓ ' + file.name : '✗ Ошибка';
    })
    .catch(() => {
        status.textContent = '✗ Ошибка загрузки';
    });
}
</script>

