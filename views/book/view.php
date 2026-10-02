<?php

/** @var app\models\BookSection $section */
/** @var app\models\BookChapter $chapter */
/** @var app\models\BookPage $page */
/** @var app\models\BookPage|null $prev */
/** @var app\models\BookPage|null $next */
/** @var array $rendered */

use yii\helpers\Html;
use app\assets\KatexAsset;
use app\assets\PrismAsset;

KatexAsset::register($this);
PrismAsset::register($this);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6 flex-wrap">
        <a href="/book" class="hover:text-acid-lime no-underline">Учебник</a>
        <span>/</span>
        <a href="/book/<?= $section->slug ?>" class="hover:text-acid-lime no-underline">
            <?= Html::encode($section->title) ?>
        </a>
        <span>/</span>
        <span class="text-base-100"><?= Html::encode($chapter->title) ?></span>
        <span>/</span>
        <span class="text-base-100"><?= Html::encode($page->title) ?></span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">

        <aside class="lg:sticky thin-scrollbar w-full lg:shrink-0" style="top: 84px;" id="book-sidebar">

            <button type="button"
                class="lg:hidden w-full flex items-center justify-between px-4 py-3 rounded-xl bg-white mb-3"
                style="border: 1px solid #E2E8F0;"
                onclick="document.getElementById('book-sidebar-content').classList.toggle('hidden')">
                <span class="text-sm font-medium text-base-100">Оглавление раздела</span>
                <svg class="w-4 h-4 text-base-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div id="book-sidebar-content" class="hidden lg:block lg:max-h-[calc(100vh-100px)] lg:overflow-y-auto thin-scrollbar">

                <?php
                $allChapters = \app\models\BookChapter::find()
                    ->where(['section_id' => $section->id, 'parent_id' => null])
                    ->orderBy('sort_order')
                    ->with('pages', 'children.pages')
                    ->all();
                ?>

                <div class="space-y-1">
                    <?php foreach ($allChapters as $navChapter): ?>
                        <?php
                        $chapterPages = array_merge(
                            $navChapter->pages,
                            array_merge(...array_map(fn($c) => $c->pages, $navChapter->children ?: []))
                        );
                        $isCurrentChapter = in_array($page->id, array_map(fn($p) => $p->id, $chapterPages));
                        ?>
                        <details class="book-nav-chapter" <?= $isCurrentChapter ? 'open' : '' ?>>
                            <summary class="book-nav-chapter-title">
                                <?= Html::encode($navChapter->title) ?>
                            </summary>

                            <div class="book-nav-pages">
                                <?php foreach ($navChapter->pages as $p): ?>
                                    <?php if (!$p->isPublished()) continue; ?>
                                    <?php $isActive = $p->id === $page->id; ?>

                                    <a href="<?= $p->getUrl() ?>" class="book-nav-page <?= $isActive ? 'is-active' : '' ?>">
                                        <?= Html::encode($p->title) ?>
                                    </a>

                                    <?php if ($isActive && $rendered['headings']): ?>
                                        <div class="book-nav-page-toc">
                                            <?php foreach ($rendered['headings'] as $h): ?>
                                                <a href="#<?= $h['slug'] ?>" class="<?= $h['level'] === 3 ? 'is-h3' : '' ?>">
                                                    <?= Html::encode($h['text']) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php foreach ($navChapter->children as $child): ?>
                                    <p class="book-nav-subchapter-title"><?= Html::encode($child->title) ?></p>
                                    <?php foreach ($child->pages as $p): ?>
                                        <?php if (!$p->isPublished()) continue; ?>
                                        <?php $isActive = $p->id === $page->id; ?>

                                        <a href="<?= $p->getUrl() ?>" class="book-nav-page book-nav-page-sub <?= $isActive ? 'is-active' : '' ?>">
                                            <?= Html::encode($p->title) ?>
                                        </a>

                                        <?php if ($isActive && $rendered['headings']): ?>
                                            <div class="book-nav-page-toc">
                                                <?php foreach ($rendered['headings'] as $h): ?>
                                                    <a href="#<?= $h['slug'] ?>" class="<?= $h['level'] === 3 ? 'is-h3' : '' ?>">
                                                        <?= Html::encode($h['text']) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>

            </div>
        </aside>

        <main class="flex-1 min-w-0 pr-2 lg:pr-6">

            <h1 class="text-3xl font-bold text-base-100 mb-8"><?= Html::encode($page->title) ?></h1>

            <div class="prose-task" style="max-width: 760px;">
                <?= $rendered['html'] ?>
            </div>

            <div class="flex justify-between gap-4 mt-12 pt-6" style="border-top: 1px solid #E2E8F0;">
                <?php if ($prev): ?>
                    <a href="<?= $prev->getUrl() ?>"
                        class="flex items-center gap-2 text-sm text-base-400 hover:text-acid-lime no-underline group max-w-[45%]">
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-150 group-hover:-translate-x-1"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        <span class="min-w-0">
                            <?php if ($prev->chapter_id !== $page->chapter_id): ?>
                                <span class="block text-xs text-base-400/70 truncate"><?= Html::encode($prev->chapter->title ?? '') ?></span>
                            <?php endif; ?>
                            <span class="block truncate"><?= Html::encode($prev->title) ?></span>
                        </span>
                    </a>
                <?php else: ?><div></div><?php endif; ?>

                <?php if ($next): ?>
                    <a href="<?= $next->getUrl() ?>"
                        class="flex items-center gap-2 text-sm text-base-400 hover:text-acid-lime no-underline group max-w-[45%] text-right justify-end">
                        <span class="min-w-0">
                            <?php if ($next->chapter_id !== $page->chapter_id): ?>
                                <span class="block text-xs text-base-400/70 truncate"><?= Html::encode($next->chapter->title ?? '') ?></span>
                            <?php endif; ?>
                            <span class="block truncate"><?= Html::encode($next->title) ?></span>
                        </span>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-150 group-hover:translate-x-1"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                <?php else: ?><div></div><?php endif; ?>
            </div>

        </main>

    </div>
</div>

<button id="back-to-top"
    class="fixed bottom-6 right-6 w-11 h-11 rounded-full bg-white shadow-card-hover
               flex items-center justify-center text-base-400 hover:text-acid-lime
               transition-all duration-200 opacity-0 pointer-events-none z-40"
    style="border: 1px solid #E2E8F0;"
    onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
    </svg>
</button>

<script>
    (function() {
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                btn.classList.remove('opacity-0', 'pointer-events-none');
                btn.classList.add('opacity-100');
            } else {
                btn.classList.add('opacity-0', 'pointer-events-none');
                btn.classList.remove('opacity-100');
            }
        });

        const tocLinks = document.querySelectorAll('.book-nav-page-toc a');
        if (!tocLinks.length) return;

        const headingEls = Array.from(tocLinks)
            .map(link => document.getElementById(link.getAttribute('href').slice(1)))
            .filter(Boolean);

        function updateActiveHeading() {
            let currentId = null;
            const offset = 110;
            for (const el of headingEls) {
                if (el.getBoundingClientRect().top - offset <= 0) currentId = el.id;
            }
            tocLinks.forEach(link => {
                const isActive = currentId && link.getAttribute('href') === '#' + currentId;
                link.classList.toggle('is-active-scroll', isActive);
            });
        }

        window.addEventListener('scroll', updateActiveHeading, {
            passive: true
        });
        updateActiveHeading();
    })();
</script>