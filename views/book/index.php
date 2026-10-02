<?php
/** @var app\models\BookSection[] $sections */
use yii\helpers\Html;

$this->title = 'Учебник';

$palette = [
    ['grad' => 'linear-gradient(145deg, #EEF2FF 0%, #E0E7FF 100%)', 'border' => '#C7D2FE', 'accent' => '#4F46E5', 'blob' => 'rgba(79,70,229,0.18)'],
    ['grad' => 'linear-gradient(145deg, #ECFDF5 0%, #D1FAE5 100%)', 'border' => '#A7F3D0', 'accent' => '#059669', 'blob' => 'rgba(5,150,105,0.16)'],
    ['grad' => 'linear-gradient(145deg, #FDF4FF 0%, #FAE8FF 100%)', 'border' => '#F0ABFC', 'accent' => '#A21CAF', 'blob' => 'rgba(162,28,175,0.16)'],
    ['grad' => 'linear-gradient(145deg, #FFF7ED 0%, #FFEDD5 100%)', 'border' => '#FED7AA', 'accent' => '#C2410C', 'blob' => 'rgba(194,65,12,0.16)'],
    ['grad' => 'linear-gradient(145deg, #ECFEFF 0%, #CFFAFE 100%)', 'border' => '#A5F3FC', 'accent' => '#0E7490', 'blob' => 'rgba(14,116,144,0.16)'],
];
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Учебник</h1>
        <p class="text-base-400">Выберите раздел для изучения</p>
    </div>

    <?php if (empty($sections)): ?>
        <div class="card text-center py-16"><p class="text-base-400">Учебник пока не наполнен.</p></div>
    <?php else: ?>
        <div class="grid grid-cols-2 gap-5">
            <?php foreach ($sections as $i => $section): ?>
                <?php
                $c = $palette[$i % count($palette)];
                $firstPage = $section->getFirstPage();
                // Если в разделе ещё нет ни одной опубликованной страницы —
                // ведём на список тем раздела (там можно будет создать контент), а не на пустоту.
                $href = $firstPage ? $firstPage->getUrl() : '/book/' . $section->slug;
                ?>
                <a href="<?= $href ?>"
                   class="relative rounded-2xl p-6 overflow-hidden no-underline block"
                   style="background: <?= $c['grad'] ?>; border: 1px solid <?= $c['border'] ?>; transition: transform .2s, box-shadow .2s;"
                   onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 14px 32px <?= $c['blob'] ?>';"
                   onmouseout="this.style.transform='none'; this.style.boxShadow='none';">

                    <div style="position:absolute; top:-30px; right:-30px; width:120px; height:120px; border-radius:50%;
                                background: radial-gradient(circle, <?= $c['blob'] ?>, transparent 70%);"></div>

                    <h2 class="text-xl font-bold mb-2 relative z-10" style="color: <?= $c['accent'] ?>;">
                        <?= Html::encode($section->title) ?>
                    </h2>
                    <?php if ($section->description): ?>
                        <p class="text-sm text-base-400 mb-4 relative z-10">
                            <?= Html::encode($section->description) ?>
                        </p>
                    <?php endif; ?>
                    <span class="relative z-10 inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full"
                          style="background: white; color: <?= $c['accent'] ?>;">
                        <?= $section->getPageCount() ?> страниц
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>