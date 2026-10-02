<?php
/** @var yii\web\View $this */
/** @var string $content */
use yii\helpers\Html;
use app\assets\SlideAsset;

SlideAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <?= Html::csrfMetaTags() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title ?? 'Презентация') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <?php $this->head() ?>
</head>
<body class="slide-body">
<?php $this->beginBody() ?>

    <?= $content ?>

    <!-- Полоска прогресса -->
    <div style="position:relative;">
        <div id="slide-progress" class="slide-progress"></div>
    </div>

    <!-- Сцена слайда -->
    <main id="slide-stage" class="slide-stage">
        <div id="slide-inner" class="slide-inner"></div>
    </main>

    <!-- Панель управления -->
    <div class="slide-controls">
        <button id="btn-prev" class="slide-btn">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
            Назад
        </button>

        <div style="display:flex; align-items:center; gap:12px;">
            <span id="slide-counter" class="slide-counter">— / —</span>
            <button id="btn-notes" class="slide-mini-btn">Заметки</button>
            <button id="btn-fullscreen" class="slide-mini-btn">[ F ]</button>
        </div>

        <button id="btn-next" class="slide-btn">
            Вперёд
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
    </div>

    <!-- Панель заметок -->
    <div id="notes-panel" class="slide-notes-panel hidden">
        <p class="slide-notes-label">Заметки учителя</p>
        <div id="notes-content" class="slide-notes-content"></div>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>