<?php
/** @var array $items */
use yii\helpers\Html;

$typeLabels = [
    'session'  => 'Занятие',
    'homework' => 'Домашнее задание',
    'exam'     => 'Экзамен',
];

$statusColor = function ($type, $status) {
    if ($type === 'session') {
        return $status === 'completed' ? 'badge-indigo' : ($status === 'cancelled' ? 'badge-red' : 'badge-gray');
    }
    if ($type === 'homework') {
        return in_array($status, ['submitted', 'reviewed']) ? 'badge-indigo' : 'badge-gray';
    }
    return in_array($status, ['submitted', 'expired']) ? 'badge-indigo' : 'badge-gray';
};

$statusText = function ($type, $status) {
    $map = [
        'session'  => \app\models\ClassSession::getStatusLabels(),
        'homework' => \app\models\HomeworkStudent::getLabels(),
        'exam'     => ['in_progress' => 'В процессе', 'submitted' => 'Сдан', 'expired' => 'Истёк по времени'],
    ];
    return $map[$type][$status] ?? $status;
};
?>

<?php if (empty($items)): ?>
    <div class="card text-center py-16"><p class="text-base-400">История пока пуста.</p></div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($items as $item): ?>
            <div class="card flex items-center gap-4">
                <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                             <?= $item['type'] === 'session' ? 'bg-acid-cyan/15 text-acid-cyan' : '' ?>
                             <?= $item['type'] === 'homework' ? 'bg-acid-lime/15 text-acid-lime' : '' ?>
                             <?= $item['type'] === 'exam' ? 'bg-acid-violet/15 text-acid-violet' : '' ?>">
                    <?= $item['type'] === 'session' ? '📅' : ($item['type'] === 'homework' ? '📝' : '🎯') ?>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-base-400"><?= $typeLabels[$item['type']] ?></p>
                    <p class="font-medium text-base-100 truncate"><?= Html::encode($item['title']) ?></p>
                    <p class="text-xs text-base-400 mt-0.5">
                        <?= Yii::$app->formatter->asDatetime($item['date'], 'php:d.m.Y H:i') ?>
                    </p>
                </div>
                <span class="<?= $statusColor($item['type'], $item['status']) ?> shrink-0">
                    <?= $statusText($item['type'], $item['status']) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>