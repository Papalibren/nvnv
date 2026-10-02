<?php
/** @var app\models\BookSection $section */
/** @var app\models\BookChapter[] $chapters */
use yii\helpers\Html;

$this->title = $section->title;

$palette = [
    ['bg' => '#EEF2FF', 'border' => '#C7D2FE', 'accent' => '#4F46E5'],
    ['bg' => '#ECFDF5', 'border' => '#A7F3D0', 'accent' => '#059669'],
    ['bg' => '#FDF4FF', 'border' => '#F0ABFC', 'accent' => '#A21CAF'],
    ['bg' => '#FFF7ED', 'border' => '#FED7AA', 'accent' => '#C2410C'],
    ['bg' => '#ECFEFF', 'border' => '#A5F3FC', 'accent' => '#0E7490'],
];
?>

<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/book" class="hover:text-acid-lime no-underline">Учебник</a>
        <span>/</span>
        <span class="text-base-100"><?= Html::encode($section->title) ?></span>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-100 mb-1"><?= Html::encode($section->title) ?></h1>
        <?php if ($section->description): ?>
            <p class="text-base-400"><?= Html::encode($section->description) ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($chapters)): ?>
        <div class="card text-center py-12"><p class="text-base-400">Раздел пока пуст.</p></div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($chapters as $i => $chapter): ?>
                <?php
                $c = $palette[$i % count($palette)];
                $pageCount = $chapter->getPageCount();
                $firstPage = $chapter->getFirstPage();
                $href = $firstPage ? $firstPage->getUrl() : null;
                ?>
                <?php if ($href): ?>
                    <a href="<?= $href ?>"
                       class="rounded-2xl p-5 no-underline block transition-transform duration-150 hover:-translate-y-0.5"
                       style="background: <?= $c['bg'] ?>; border: 1px solid <?= $c['border'] ?>;">
                        <h2 class="text-lg font-bold mb-1" style="color: <?= $c['accent'] ?>;">
                            <?= Html::encode($chapter->title) ?>
                        </h2>
                        <p class="text-sm text-base-400">
                            <?= $pageCount ?> <?= $pageCount == 1 ? 'страница' : ($pageCount < 5 ? 'страницы' : 'страниц') ?>
                        </p>
                    </a>
                <?php else: ?>
                    <div class="rounded-2xl p-5 block opacity-50"
                         style="background: <?= $c['bg'] ?>; border: 1px solid <?= $c['border'] ?>;">
                        <h2 class="text-lg font-bold mb-1" style="color: <?= $c['accent'] ?>;">
                            <?= Html::encode($chapter->title) ?>
                        </h2>
                        <p class="text-sm text-base-400">Пока нет опубликованных страниц</p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>