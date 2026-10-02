<?php

/** @var yii\web\View $this */
/** @var app\models\forms\TaskForm $form */
/** @var app\models\TaskTag[] $tags */
/** @var app\models\TheoryTopic[] $topics */
/** @var app\models\Task|null $task */
/** @var bool $isNew */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\assets\KatexAsset;

KatexAsset::register($this);
?>

<!-- Хлебные крошки -->
<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/task/index']) ?>"
        class="hover:text-base-100 transition-colors duration-150 no-underline">
        Задачи
    </a>
    <span>/</span>
    <span class="text-base-100">
        <?= $isNew ? 'Новая задача' : 'Задача #' . $form->id ?>
    </span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-6">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php $activeForm = ActiveForm::begin([
    'options'                => ['enctype' => 'multipart/form-data'],
    'enableClientValidation' => false,
    'fieldConfig'            => [
        'template'     => '{label}{input}{error}',
        'labelOptions' => ['class' => ''],
        'inputOptions' => ['class' => 'input'],
        'errorOptions' => ['class' => 'field-error', 'tag' => 'p'],
    ],
]) ?>

<div class="grid grid-cols-3 gap-6">

    <!-- Левая колонка — основное -->
    <div class="col-span-2 space-y-5">

        <!-- Условие задачи -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-base-100">Условие задачи</h2>
                <button type="button"
                    onclick="openMediaModal('taskform-content')"
                    class="btn-secondary text-xs py-1.5 px-3">
                    + Изображение / файл
                </button>
            </div>

            <div class="field-group">
                <?= $activeForm->field($form, 'content')->textarea([
                    'rows'        => 8,
                    'placeholder' => 'Текст условия. Используйте $формула$ для KaTeX.',
                    'class'       => 'input font-mono text-sm',
                    'id'          => 'taskform-content',
                ])->label('Условие *') ?>
            </div>

            <div class="mt-3">
                <p class="text-xs text-base-400 mb-2">Предпросмотр:</p>
                <div id="content-preview"
                    class="p-4 rounded-lg border border-base-700 bg-base-950 min-h-16 text-sm">
                </div>
            </div>
        </div>

        <!-- Разбор -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-base-100">Разбор решения</h2>
                <button type="button"
                    onclick="openMediaModal('taskform-solution_content')"
                    class="btn-secondary text-xs py-1.5 px-3">
                    + Изображение / файл
                </button>
            </div>

            <div class="field-group">
                <?= $activeForm->field($form, 'solution_content')->textarea([
                    'rows'        => 6,
                    'placeholder' => 'Подробный разбор с объяснением...',
                    'class'       => 'input font-mono text-sm',
                    'id'          => 'taskform-solution_content',
                ])->label('Разбор') ?>
            </div>

            <div class="field-group mb-0">
                <label class="checkbox-label">
                    <?= Html::activeCheckbox($form, 'solution_is_public', [
                        'label' => false,
                    ]) ?>
                    <span>Показывать разбор публично</span>
                </label>
            </div>
        </div>

    </div>

    <!-- Правая колонка — параметры -->
    <div class="space-y-5">

        <!-- Публикация -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Публикация</h2>

            <div class="field-group">
                <?= $activeForm->field($form, 'status')->dropDownList([
                    'draft'     => 'Черновик',
                    'published' => 'Опубликована',
                ], ['class' => 'input'])->label('Статус') ?>
            </div>

            <div class="flex gap-2">
                <?= Html::submitButton(
                    $isNew ? 'Создать задачу' : 'Сохранить',
                    ['class' => 'btn-primary flex-1']
                ) ?>
                <a href="<?= Url::to(['/admin/task/index']) ?>"
                    class="btn-secondary px-3">✕</a>
            </div>
        </div>

        <!-- Параметры -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Параметры</h2>

            <div class="field-group">
                <?= $activeForm->field($form, 'task_number')->dropDownList(
                    ['' => 'Без номера (общая задача)'] + array_combine(range(1, 27), range(1, 27)),
                    ['class' => 'input', 'prompt' => '']
                )->label('Номер задания ЕГЭ') ?>
            </div>

            <div class="field-group">
                <label>Тип ответа</label>
                <div class="flex gap-2 mb-2">
                    <label class="flex-1">
                        <input type="radio" name="TaskForm[answer_type]" value="exact" class="hidden peer" id="at-exact"
                            <?= $form->answer_type !== 'manual' ? 'checked' : '' ?> onchange="toggleAnswerType()">
                        <span class="block text-center py-2 rounded-lg border cursor-pointer text-xs font-medium
                         peer-checked:bg-acid-lime peer-checked:text-white peer-checked:border-acid-lime"
                            style="border-color:#E2E8F0;">Точный ответ</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="TaskForm[answer_type]" value="manual" class="hidden peer" id="at-manual"
                            <?= $form->answer_type === 'manual' ? 'checked' : '' ?> onchange="toggleAnswerType()">
                        <span class="block text-center py-2 rounded-lg border cursor-pointer text-xs font-medium
                         peer-checked:bg-acid-violet peer-checked:text-white peer-checked:border-acid-violet"
                            style="border-color:#E2E8F0;">Развёрнутый (проверка вручную)</span>
                    </label>
                </div>

                <div id="answer-exact-block">
                    <?= $activeForm->field($form, 'answer')->textInput([
                        'placeholder' => 'Точный ответ',
                        'class'       => 'input font-mono',
                    ])->label('Правильный ответ') ?>
                </div>

                <p id="answer-manual-hint" class="text-xs text-base-400 hidden">
                    Точного ответа нет (например таблица истинности, построение графа).
                    Ученик вводит любой текст, оценку по разбору ставит учитель вручную в проверке ДЗ/экзамена.
                    Обязательно заполните поле «Разбор решения» ниже.
                </p>
            </div>

            <script>
                function toggleAnswerType() {
                    const isManual = document.getElementById('at-manual').checked;
                    document.getElementById('answer-exact-block').classList.toggle('hidden', isManual);
                    document.getElementById('answer-manual-hint').classList.toggle('hidden', !isManual);
                }
                toggleAnswerType();
            </script>

            <div class="field-group">
                <label>Сложность: <span id="difficulty-value" class="font-semibold text-acid-lime"><?= $form->difficulty ?: 5 ?></span>/10</label>
                <input type="range"
                    name="TaskForm[difficulty]"
                    min="1" max="10" step="1"
                    value="<?= $form->difficulty ?: 5 ?>"
                    class="w-full accent-acid-lime mt-1"
                    oninput="document.getElementById('difficulty-value').textContent = this.value">
                <?= $form->hasErrors('difficulty') ? '<p class="field-error">' . Html::encode($form->getFirstError('difficulty')) . '</p>' : '' ?>
            </div>

            <div class="field-group mb-0">
                <?= $activeForm->field($form, 'title')->textInput([
                    'placeholder' => 'Необязательно',
                    'class'       => 'input',
                ])->label('Заголовок (необязательно)') ?>
            </div>
        </div>

        <!-- Теги -->
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-base-100">Теги</h2>
                <a href="<?= Url::to(['/admin/task/tags']) ?>"
                    class="text-xs text-acid-lime hover:text-acid-violet no-underline"
                    target="_blank">
                    Управлять →
                </a>
            </div>
            <?php if ($tags): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tags as $tag): ?>
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <?= Html::checkbox('TaskForm[tag_ids][]', in_array($tag->id, $form->tag_ids), [
                                'value' => $tag->id,
                                'class' => 'accent-acid-lime',
                            ]) ?>
                            <span class="text-base-400"><?= Html::encode($tag->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-base-400">
                    Нет тегов.
                    <a href="<?= Url::to(['/admin/task/tags']) ?>" class="text-acid-lime" target="_blank">Создать</a>
                </p>
            <?php endif; ?>
        </div>

        <!-- Связанные страницы учебника -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-3">Связанные страницы учебника</h2>
            <?php if ($bookPages): ?>
                <?php
                $grouped = [];
                foreach ($bookPages as $p) {
                    $sectionTitle = $p->chapter->section->title ?? '—';
                    $grouped[$sectionTitle][] = $p;
                }
                ?>
                <div class="space-y-3 max-h-56 overflow-y-auto">
                    <?php foreach ($grouped as $sectionTitle => $pages): ?>
                        <div>
                            <p class="text-xs font-semibold text-base-400 uppercase tracking-wide mb-1">
                                <?= Html::encode($sectionTitle) ?>
                            </p>
                            <?php foreach ($pages as $page): ?>
                                <label class="flex items-center gap-2 text-sm cursor-pointer py-0.5">
                                    <?= Html::checkbox('TaskForm[book_page_ids][]', in_array($page->id, $form->book_page_ids), [
                                        'value' => $page->id,
                                        'class' => 'accent-acid-lime shrink-0',
                                    ]) ?>
                                    <span class="text-base-400"><?= Html::encode($page->title) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-base-400">Страниц учебника пока нет.</p>
            <?php endif; ?>
        </div>

        <!-- Файлы -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-3">
                Файлы и изображения <span class="text-base-400 font-normal">(до 4 штук)</span>
            </h2>

            <?php if (!$isNew && $task && $task->files): ?>
                <div class="mb-3 space-y-2">
                    <?php foreach ($task->files as $file): ?>
                        <div class="flex items-center justify-between gap-2 text-sm p-2 rounded-lg bg-base-900">
                            <div class="flex items-center gap-2 min-w-0">
                                <?php if ($file->isImage()): ?>
                                    <img src="/files/<?= $file->path ?>"
                                        class="w-10 h-10 rounded object-cover shrink-0" alt="">
                                <?php else: ?>
                                    <svg class="w-5 h-5 text-base-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                <?php endif; ?>
                                <a href="/files/<?= $file->path ?>"
                                    class="text-acid-cyan hover:text-acid-lime no-underline truncate"
                                    target="_blank">
                                    <?= Html::encode($file->filename) ?>
                                </a>
                            </div>
                            <button type="button"
                                class="text-acid-pink text-xs shrink-0"
                                hx-delete="<?= Url::to(['/admin/task/delete-file', 'id' => $file->id]) ?>"
                                hx-target="closest div.flex"
                                hx-swap="outerHTML"
                                hx-confirm="Удалить файл?">
                                Удалить
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <input type="file" name="TaskForm[files][]" multiple
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                class="text-sm text-base-400 file:btn-secondary file:mr-3 file:text-xs file:py-1 w-full">
            <p class="text-xs text-base-400 mt-1.5">
                Изображения выводятся прямо в задаче. Остальные файлы — как вложения для скачивания.
            </p>
        </div>

        <div class="card">
            <h2 class="text-sm font-semibold text-base-100 mb-1">SEO</h2>
            <p class="text-xs text-base-400 mb-4">
                Необязательно. Если не заполнено — заголовок и описание для поисковиков
                сформируются автоматически из содержимого страницы.
            </p>

            <div class="field-group">
                <label>SEO-заголовок (title в поиске)</label>
                <input type="text" name="seo_title" class="input"
                    placeholder="Например: Поиск в строках Python: in, find, index, count | ЕГЭ Информатика"
                    value="<?= $isNew ? '' : Html::encode($page->seo_title ?? '') ?>"
                    maxlength="255">
                <p class="text-xs text-base-400 mt-1">Заголовок H1 на самой странице не меняется — это только для поисковой выдачи.</p>
            </div>

            <div class="field-group mb-0">
                <label>SEO-описание (сниппет в поиске)</label>
                <textarea name="seo_description" class="input" rows="3" maxlength="300"
                    placeholder="150–160 символов, законченное предложение с ключевыми словами"><?= $isNew ? '' : Html::encode($page->seo_description ?? '') ?></textarea>
                <p id="seo-desc-counter" class="text-xs text-base-400 mt-1">0 символов</p>
            </div>
        </div>

    </div>
</div>
<?= $this->render('@app/views/admin/media/_modal') ?>
<?php ActiveForm::end() ?>

<script>
    const textarea = document.querySelector('[name="TaskForm[content]"]');
    const preview = document.getElementById('content-preview');
    let debounceTimer;

    function updatePreview() {
        if (!textarea || !preview) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetch('/admin/task/render-preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: 'content=' + encodeURIComponent(textarea.value),
                })
                .then(r => r.text())
                .then(html => {
                    preview.innerHTML = html;
                    if (typeof renderMathInElement !== 'undefined') {
                        renderMathInElement(preview, {
                            delimiters: [{
                                    left: '$$',
                                    right: '$$',
                                    display: true
                                },
                                {
                                    left: '$',
                                    right: '$',
                                    display: false
                                },
                            ],
                            throwOnError: false,
                        });
                    }
                });
        }, 400);
    }

    if (textarea) {
        textarea.addEventListener('input', updatePreview);
        updatePreview();
    }
</script>