<?php
/** @var app\models\BookChapter $chapter */
/** @var int $depth */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div id="chapter-<?= $chapter->id ?>"
     class="card p-0 overflow-hidden"
     style="<?= $depth > 0 ? 'margin-left: 24px;' : '' ?>">

    <!-- Заголовок главы -->
    <div class="flex items-center justify-between px-4 py-3 bg-base-900">
        <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-base-400 shrink-0" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
            <div>
                <span class="font-semibold text-base-100">
                    <?= Html::encode($chapter->title) ?>
                </span>
                <span class="text-xs text-base-400 ml-2">
                    /book/<?= $chapter->slug ?>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span id="chapter-status-<?= $chapter->id ?>">
                <?= $this->render('_chapter_status', ['chapter' => $chapter]) ?>
            </span>
            <span class="badge-gray">
                <?= count($chapter->pages) ?> стр.
            </span>
            <a href="<?= Url::to(['/admin/content/update-chapter', 'id' => $chapter->id]) ?>"
               class="btn-ghost text-xs">
                Изменить
            </a>
            <a href="<?= Url::to(['/admin/content/create-page', 'chapterId' => $chapter->id]) ?>"
               class="btn-ghost text-xs text-acid-lime">
                + Страница
            </a>
            <button type="button"
                    class="btn-ghost text-xs text-acid-pink"
                    hx-delete="<?= Url::to(['/admin/content/delete-chapter', 'id' => $chapter->id]) ?>"
                    hx-target="#chapter-<?= $chapter->id ?>"
                    hx-swap="outerHTML swap:300ms"
                    hx-confirm="Удалить главу «<?= Html::encode($chapter->title) ?>»?">
                Удалить
            </button>
        </div>
    </div>

    <!-- Страницы главы -->
<?php if ($chapter->pages): ?>
    <div class="divide-y divide-base-700 page-sort-list" data-chapter-id="<?= $chapter->id ?>">
        <?php foreach ($chapter->pages as $page): ?>
            <div id="page-<?= $page->id ?>"
                 class="flex items-center justify-between px-4 py-2.5 hover:bg-base-950
                        transition-colors duration-150 page-sort-item"
                 draggable="true"
                 data-page-id="<?= $page->id ?>">

                <div class="flex items-center gap-2 min-w-0">
                    <span class="drag-handle text-base-400 shrink-0 cursor-grab" title="Перетащить">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                        </svg>
                    </span>
                    <a href="<?= Url::to(['/admin/content/update-page', 'id' => $page->id]) ?>"
                       class="text-sm text-base-100 hover:text-acid-lime no-underline truncate">
                        <?= Html::encode($page->title) ?>
                    </a>
                    <span class="text-xs text-base-400 shrink-0 hidden md:inline">
                        /book/<?= $page->slug ?>
                    </span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span id="page-status-<?= $page->id ?>">
                        <?= $this->render('_page_status', ['page' => $page]) ?>
                    </span>

                    <a href="<?= Url::to(['/admin/content/update-page', 'id' => $page->id]) ?>"
                       class="btn-ghost text-xs">
                        Изменить
                    </a>

                    <button type="button"
                            class="btn-ghost text-xs text-acid-pink"
                            hx-delete="<?= Url::to(['/admin/content/delete-page', 'id' => $page->id]) ?>"
                            hx-target="#page-<?= $page->id ?>"
                            hx-swap="outerHTML swap:300ms"
                            hx-confirm="Удалить страницу «<?= Html::encode(addslashes($page->title)) ?>»?">
                        Удалить
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
        <div class="px-4 py-3 text-sm text-base-400">
            Страниц пока нет.
            <a href="<?= Url::to(['/admin/content/create-page', 'chapterId' => $chapter->id]) ?>"
               class="text-acid-lime ml-1">
                Добавить →
            </a>
        </div>
    <?php endif; ?>

</div>

<!-- Подглавы -->
<?php foreach ($chapter->children as $child): ?>
    <?= $this->render('_chapter_row', ['chapter' => $child, 'depth' => $depth + 1]) ?>
<?php endforeach; ?>