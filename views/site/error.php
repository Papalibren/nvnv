<?php
/** @var yii\web\View $this */
/** @var Exception $exception */
use yii\helpers\Html;

$code    = $exception->statusCode ?? 500;
$message = $exception->getMessage() ?: 'Произошла ошибка.';
$this->title = "Ошибка {$code}";
?>

<div class="max-w-lg mx-auto mt-24 text-center px-4">
    <div class="text-6xl font-bold grad-text mb-4"><?= $code ?></div>
    <h1 class="text-2xl font-bold text-base-100 mb-2">
        <?= $code === 404 ? 'Страница не найдена' : 'Что-то пошло не так' ?>
    </h1>
    <p class="text-base-400 mb-8">
        <?= $code === 404
            ? 'Страница которую вы ищете не существует или была перемещена.'
            : Html::encode($message) ?>
    </p>
    <a href="/" class="btn-primary">На главную</a>
</div>