<?php
/** @var yii\web\View $this */
/** @var string $content */
use yii\helpers\Html;
use app\assets\AppAsset;
use app\assets\AdminAsset;

AppAsset::register($this);
AdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <?= Html::csrfMetaTags() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title ?? 'Админка') ?> — ЕГЭ Информатика</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body class="bg-base-900 text-base-100 min-h-screen flex">
<?php $this->beginBody() ?>

    <?= $this->render('//partials/_sidebar') ?>

    <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
        <?= $this->render('//partials/_topbar') ?>
        <div class="px-6 py-1.5 flex items-center gap-2"
             style="background: rgba(79,70,229,0.06); border-bottom: 1px solid rgba(79,70,229,0.15);">
            <span class="text-xs font-semibold text-acid-lime">ADMIN</span>
            <span class="text-xs text-base-400">— полный доступ</span>
        </div>
        <main class="flex-1 p-6 overflow-y-auto">
            <?= $content ?>
        </main>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>