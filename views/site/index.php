<?php
/** @var yii\web\View $this */
/** @var int|null $daysUntilEge */
use yii\helpers\Html;

if ($daysUntilEge !== null) {
    $mod10  = $daysUntilEge % 10;
    $mod100 = $daysUntilEge % 100;

    if ($mod10 === 1 && $mod100 !== 11) {
        $noun = 'день'; $verb = 'Остался';
    } elseif (in_array($mod10, [2, 3, 4]) && !in_array($mod100, [12, 13, 14])) {
        $noun = 'дня'; $verb = 'Осталось';
    } else {
        $noun = 'дней'; $verb = 'Осталось';
    }
}
?>

<div class="max-w-6xl mx-auto px-4 py-14">

    <!-- Презентационный блок -->
    <div class="text-center mb-14 card grad-lime-cyan">
        <h1 class="text-4xl font-bold text-base-100 mb-4 leading-tight">
            Готовимся к <span class="grad-text">ЕГЭ по информатике</span> вместе
        </h1>
        <p class="text-lg text-base-400 mb-8 max-w-xl mx-auto">
            Теория, практика, пробные экзамены и работа с репетитором — всё в одном месте.
        </p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="/tasks" class="btn-primary px-6 py-3">Смотреть задачи</a>
            <a href="/book" class="btn-secondary px-6 py-3">Открыть учебник</a>
        </div>
    </div>

    <!-- Основная раскладка: слева место под будущий контент, справа виджеты -->
    <div class="home-layout">

        <div class="home-main">
            <!-- Пусто — контент будет добавлен позже -->
        </div>

        <aside class="home-sidebar">

            <?php if ($daysUntilEge !== null): ?>
            <div class="card text-center mb-4">
                <p class="text-xs uppercase tracking-wider text-base-400 mb-3">
                    До ЕГЭ по информатике
                </p>
                <p class="text-5xl font-extrabold grad-text mb-1"><?= $daysUntilEge ?></p>
                <p class="text-sm text-base-400"><?= $verb ?> <?= $noun ?></p>
            </div>
            <?php endif; ?>

        </aside>

    </div>
</div>