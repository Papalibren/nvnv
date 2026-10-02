<?php

/** @var yii\web\View $this */
/** @var app\models\BookPage|null $page */
/** @var app\models\BookChapter[] $chapters */
/** @var int|null $chapterId */
/** @var string|null $error */
/** @var bool $isNew */

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\KatexAsset;
use app\assets\PrismAsset;


KatexAsset::register($this);
PrismAsset::register($this);

$title   = $isNew ? 'Новая страница' : ($page ? $page->title : 'Страница');
$content = $isNew ? '' : ($page ? $page->content : '');
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/content/book']) ?>"
        class="hover:text-base-100 no-underline">Учебник</a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4">
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
<?php endif; ?>

<form method="post" id="page-form">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 280px;">

        <!-- Контент -->
        <div class="space-y-4">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-base-100">Содержимое</h2>
                    <div class="flex gap-2">
                        <div class="relative">
                            <button type="button" onclick="document.getElementById('block-menu').classList.toggle('hidden')"
                                class="btn-secondary text-xs py-1.5 px-3">
                                + Блок ▾
                            </button>
                            <div id="block-menu" class="hidden absolute right-0 mt-1 bg-white rounded-lg shadow-card-hover z-10"
                                style="border:1px solid #E2E8F0; min-width:180px;">
                                <button type="button" onclick="insertBlock('example')" class="block w-full text-left px-3 py-2 text-sm hover:bg-base-900">💡 Пример</button>
                                <button type="button" onclick="insertBlock('definition')" class="block w-full text-left px-3 py-2 text-sm hover:bg-base-900">🔖 Определение</button>
                                <button type="button" onclick="insertBlock('note')" class="block w-full text-left px-3 py-2 text-sm hover:bg-base-900">ℹ️ Заметка</button>
                                <button type="button" onclick="insertBlock('warning')" class="block w-full text-left px-3 py-2 text-sm hover:bg-base-900">⚠️ Важно</button>
                                <button type="button" onclick="insertToc()" class="block w-full text-left px-3 py-2 text-sm hover:bg-base-900" style="border-top:1px solid #E2E8F0;">📑 Оглавление</button>
                            </div>
                        </div>
                        <button type="button"
                            onclick="openMediaModal('page-content')"
                            class="btn-secondary text-xs py-1.5 px-3">
                            + Изображение / файл
                        </button>
                    </div>
                </div>

                <div class="field-group">
                    <label>Контент (Markdown + KaTeX)</label>
                    <textarea name="content"
                        id="page-content"
                        rows="20"
                        class="input font-mono text-sm"
                        placeholder="# Заголовок&#10;&#10;Текст страницы. Используйте $формула$ для KaTeX."
                        oninput="updatePreview()"><?= Html::encode($content) ?></textarea>
                </div>
            </div>

            <!-- Предпросмотр -->
            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-3">Предпросмотр</h2>
                <div id="content-preview"
                    class="prose-task text-sm min-h-32 text-base-100">
                </div>
            </div>
        </div>

        <!-- Правая колонка -->
        <div class="space-y-4">

            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-4">Параметры</h2>

                <div class="field-group">
                    <label>Заголовок *</label>
                    <input type="text" name="title" class="input" required autofocus
                        value="<?= $isNew ? '' : Html::encode($page->title ?? '') ?>">
                </div>

                <div class="field-group">
                    <label>Глава *</label>
                    <select name="chapter_id" class="input">
                        <option value="">— Выберите главу —</option>
                        <?php
                        $bySection = [];
                        foreach ($chapters as $ch) {
                            $bySection[$ch->section->title ?? '—'][] = $ch;
                        }
                        ?>
                        <?php foreach ($bySection as $sectionTitle => $sectionChapters): ?>
                            <optgroup label="<?= Html::encode($sectionTitle) ?>">
                                <?php foreach ($sectionChapters as $ch): ?>
                                    <?php
                                    $selected = $chapterId == $ch->id
                                        || (!$isNew && $page && $page->chapter_id == $ch->id);
                                    $prefix = $ch->parent_id ? '  └ ' : '';
                                    ?>
                                    <option value="<?= $ch->id ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= $prefix . Html::encode($ch->title) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label>Порядок</label>
                    <input type="number" name="sort_order" class="input"
                        value="<?= $isNew ? 0 : ($page->sort_order ?? 0) ?>">
                </div>

                <div class="field-group mb-0">
                    <label class="checkbox-label">
                        <input type="checkbox" name="published" value="1"
                            class="accent-acid-lime"
                            <?= (!$isNew && $page && $page->isPublished()) ? 'checked' : '' ?>>
                        <span>Опубликована</span>
                    </label>
                </div>
            </div>
<div class="card">
    <div class="field-group">
        <label>SEO-заголовок</label>
        <input type="text" name="seo_title" class="input"
               placeholder=""
               value="<?= $isNew ? '' : Html::encode($page->seo_title ?? '') ?>"
               maxlength="255">
    </div>

    <div class="field-group mb-0">
        <label>SEO-описание</label>
        <textarea name="seo_description" class="input" rows="3" maxlength="300"><?= $isNew ? '' : Html::encode($page->seo_description ?? '') ?></textarea>
        <p id="seo-desc-counter" class="text-xs text-base-400 mt-1">0 символов</p>
    </div>
</div>
<div class="card">
    <h2 class="text-sm font-semibold text-base-100 mb-3">URL страницы</h2>

    <div class="field-group mb-3">
        <label>Slug (часть адреса)</label>
        <input type="text" name="slug" class="input font-mono text-sm"
               value="<?= $isNew ? '' : Html::encode($page->slug) ?>"
               placeholder="<?= $isNew ? 'сгенерируется из заголовка автоматически' : '' ?>">
        <?php if (!$isNew): ?>
            <p class="text-xs text-acid-pink mt-1.5">
                Изменение URL сломает уже сохранённые ссылки на эту страницу — меняйте только если уверены.
            </p>
        <?php else: ?>
            <p class="text-xs text-base-400 mt-1.5">Оставьте пустым — сгенерируется из заголовка.</p>
        <?php endif; ?>
    </div>

    <?php if (!$isNew && $page): ?>
        <a href="<?= $page->getUrl() ?>"
           target="_blank"
           class="text-xs text-acid-cyan break-all no-underline hover:text-acid-lime">
            <?= $page->getUrl() ?>
        </a>
    <?php endif; ?>
</div>

            <div class="flex gap-2">
                <?= Html::submitButton(
                    $isNew ? 'Создать страницу' : 'Сохранить',
                    ['class' => 'btn-primary flex-1']
                ) ?>
                <a href="<?= Url::to(['/admin/content/book']) ?>"
                    class="btn-secondary px-3">✕</a>
            </div>

        </div>
    </div>
</form>

<?= $this->render('@app/views/admin/media/_modal') ?>

<script>
    let previewTimer;

    function updatePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(() => {
            const content = document.getElementById('page-content').value;
            const preview = document.getElementById('content-preview');
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch('/admin/content/render-preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': token,
                    },
                    body: 'content=' + encodeURIComponent(content),
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
        }, 500);
    }
const calloutTemplates = {
    example:    ':::example Заголовок примера\nТекст примера...\n:::',
    definition: ':::definition Термин\nОпределение термина...\n:::',
    note:       ':::note\nТекст заметки...\n:::',
    warning:    ':::warning\nТекст предупреждения...\n:::',
};

function insertBlock(type) {
    const textarea = document.getElementById('page-content');
    insertAtCursor(textarea, '\n\n' + calloutTemplates[type] + '\n\n');
    document.getElementById('block-menu').classList.add('hidden');
    updatePreview();
}

function insertToc() {
    const textarea = document.getElementById('page-content');
    insertAtCursor(textarea, '\n\n[[toc]]\n\n');
    document.getElementById('block-menu').classList.add('hidden');
    updatePreview();
}

function insertAtCursor(textarea, text) {
    const start = textarea.selectionStart;
    const end   = textarea.selectionEnd;
    const value = textarea.value;

    const before = value.substring(0, start);
    const after  = value.substring(end);

    // Не дублируем перенос строки если он уже есть перед/после точки вставки
    const needsLeadingBreak  = before.length > 0 && !before.endsWith('\n\n');
    const needsTrailingBreak = after.length > 0 && !after.startsWith('\n\n');

    let insertText = text.trim();
    if (needsLeadingBreak)  insertText = '\n\n' + insertText;
    if (needsTrailingBreak) insertText = insertText + '\n\n';

    textarea.value = before + insertText + after;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = before.length + insertText.length;
}

// Закрыть меню при клике вне его
document.addEventListener('click', (e) => {
    const menu = document.getElementById('block-menu');
    if (menu && !menu.classList.contains('hidden') && !e.target.closest('.relative')) {
        menu.classList.add('hidden');
    }
});
    // Инициализация предпросмотра
    updatePreview();
const seoDescField = document.querySelector('[name="seo_description"]');
const seoDescCounter = document.getElementById('seo-desc-counter');

function updateSeoCounter() {
    const len = seoDescField.value.length;
    seoDescCounter.textContent = len + ' символов' + (len > 160 ? ' (рекомендуется до 160)' : '');
    seoDescCounter.style.color = len > 160 ? '#E11D48' : '#94A3B8';
}

if (seoDescField) {
    seoDescField.addEventListener('input', updateSeoCounter);
    updateSeoCounter();
}
</script>