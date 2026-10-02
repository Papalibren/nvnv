<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use app\assets\AppAsset;
use app\assets\CodeRunnerAsset;

CodeRunnerAsset::register($this);
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <?= Html::csrfMetaTags() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title ?? 'ЕГЭ Информатика') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <?php
    $siteSetting = \app\models\SiteSetting::current();
    ?>
    <?php \app\helpers\Seo::ensureDefaults($this) ?>

    <?php if ($siteSetting->google_search_console_code): ?>
        <meta name="google-site-verification" content="<?= Html::encode($siteSetting->google_search_console_code) ?>">
    <?php endif; ?>
    <?php if ($siteSetting->yandex_webmaster_code): ?>
        <meta name="yandex-verification" content="<?= Html::encode($siteSetting->yandex_webmaster_code) ?>">
    <?php endif; ?>

    <!-- Базовая структурированная разметка сайта -->
    <?php foreach (($this->params['jsonLd'] ?? []) as $json): ?>
        <script type="application/ld+json">
            <?= $json ?>
        </script>
    <?php endforeach; ?>
    <?php $this->head() ?>
</head>

<body class="bg-base-950 text-base-100 min-h-screen flex flex-col">
    <?php $this->beginBody() ?>
    <?= $siteSetting->getYandexMetrikaSnippet() ?>
    <?= $siteSetting->getGoogleAnalyticsSnippet() ?>

    <nav class="sticky top-0 z-50 h-14 bg-white/90 backdrop-blur-md"
        style="border-bottom: 1px solid #CBD5E1;">
        <div style="height: 3px; background: linear-gradient(90deg, #4F46E5, #0EA5E9, #A855F7);"></div>
        <div class="max-w-6xl mx-auto px-4 h-full flex items-center justify-between">
            <a href="/" class="text-lg font-bold grad-text no-underline">
                ЕГЭ Информатика
            </a>
            <div class="hidden md:flex items-center gap-6 text-sm">
                <a href="/book" class="text-base-400 hover:text-base-100 transition-colors duration-150 no-underline">Учебник</a>
                <a href="/tasks" class="text-base-400 hover:text-base-100 transition-colors duration-150 no-underline">Задачи</a>
                <a href="/python" class="text-base-400 hover:text-base-100 transition-colors duration-150 no-underline">Питон</a>
                <a href="/tools" class="text-base-400 hover:text-base-100 transition-colors duration-150 no-underline">Инструменты</a>
            </div>
            <div>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="/login" class="btn-secondary text-sm py-2 px-4">Войти</a>
                <?php else: ?>
                    <?php
                    $role = Yii::$app->user->identity->role;
                    $cabinetUrl = match ($role) {
                        'admin'   => '/admin',
                        'teacher' => '/teacher',
                        default   => '/student',
                    };
                    ?>
                    <a href="<?= $cabinetUrl ?>" class="btn-primary text-sm py-2 px-4">Кабинет</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        <?= $content ?>
    </main>

    <footer class="py-8 px-4 mt-16 bg-white" style="border-top: 1px solid #CBD5E1;">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-base-400">
            <span class="grad-text font-semibold">ЕГЭ Информатика</span>
            <div class="flex gap-6">
                <a href="/book" class="hover:text-base-100 transition-colors duration-150">Учебник</a>
                <a href="/theory" class="hover:text-base-100 transition-colors duration-150">Теория</a>
                <a href="/tasks" class="hover:text-base-100 transition-colors duration-150">Задачи</a>
            </div>
            <span><?= date('Y') ?> ЕГЭ Информатика</span>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>