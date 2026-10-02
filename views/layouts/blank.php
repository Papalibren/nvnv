<?php
/** @var yii\web\View $this */
/** @var string $content */
use yii\helpers\Html;
use app\assets\AppAsset;

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden"
      style="background: #F1F5F9;">
<?php $this->beginBody() ?>

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full"
             style="background: radial-gradient(circle, rgba(79,70,229,0.12), transparent 70%); filter: blur(40px);"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full"
             style="background: radial-gradient(circle, rgba(14,165,233,0.10), transparent 70%); filter: blur(40px);"></div>
    </div>

    <div class="w-full max-w-sm px-4 relative z-10">
        <div class="text-center mb-8">
            <a href="/" class="inline-block no-underline">
                <span class="text-2xl font-bold grad-text">ЕГЭ Информатика</span>
            </a>
            <p class="text-xs text-base-400 mt-1">Подготовка к экзамену</p>
        </div>
        <?= $content ?>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>