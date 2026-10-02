<?php
/** @var array $leaderboard */
use yii\helpers\Html;

$this->title = 'Рейтинг учеников';
?>

<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Рейтинг учеников</h1>
        <p class="text-base-400">
            Учитывается прогресс курса, публичные задачи, публичные экзамены и работа с репетитором
        </p>
    </div>

    <?php if (empty($leaderboard)): ?>
        <div class="card text-center py-16">
            <p class="text-base-400">Рейтинг пока пуст — начните проходить курс или публичные задачи.</p>
        </div>
    <?php else: ?>
        <div class="card p-0 overflow-hidden">
            <?php foreach ($leaderboard as $i => $row): ?>
                <div class="flex items-center gap-4 px-4 py-3 <?= $i > 0 ? 'border-t border-base-700' : '' ?>">
                    <span class="w-8 text-center font-bold <?= $i < 3 ? 'text-acid-lime' : 'text-base-400' ?>">
                        <?= $i + 1 ?>
                    </span>
                    <span class="flex-1 text-base-100 font-medium"><?= Html::encode($row['name']) ?></span>
                    <span class="font-bold text-acid-lime"><?= $row['total'] ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>