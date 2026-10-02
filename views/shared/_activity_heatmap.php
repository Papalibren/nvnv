<?php
/** @var array $weeks */
use app\services\ActivityHeatmapService;

$colors = [
    ActivityHeatmapService::STATUS_NONE       => '#E2E8F0',
    ActivityHeatmapService::STATUS_MISSED     => '#EF4444',
    ActivityHeatmapService::STATUS_ATTEMPTED  => '#F59E0B',
    ActivityHeatmapService::STATUS_RETRY_DONE => '#86EFAC',
    ActivityHeatmapService::STATUS_DONE       => '#16A34A',
];

$dayLabels = ['Пн', '', 'Ср', '', 'Пт', '', ''];
?>

<div class="overflow-x-auto">
    <div style="display: inline-flex; gap: 3px;">
        <!-- Подписи дней недели -->
        <div style="display: flex; flex-direction: column; gap: 3px; margin-right: 4px;">
            <?php foreach ($dayLabels as $label): ?>
                <div style="width: 24px; height: 12px; font-size: 10px; color: #94A3B8; display:flex; align-items:center;">
                    <?= $label ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($weeks as $week): ?>
            <div style="display: flex; flex-direction: column; gap: 3px;">
                <?php foreach ($week as $day): ?>
                    <?php
                    $isFuture = strtotime($day['date']) > strtotime('today');
                    ?>
                    <div title="<?= $day['date'] ?>"
                         style="width: 12px; height: 12px; border-radius: 3px;
                                background: <?= $isFuture ? 'transparent' : $colors[$day['status']] ?>;
                                <?= $isFuture ? '' : 'border: 1px solid rgba(0,0,0,0.04);' ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="flex items-center gap-4 mt-3 text-xs text-base-400 flex-wrap">
    <span class="flex items-center gap-1.5">
        <span style="width:10px;height:10px;border-radius:3px;background:#EF4444;display:inline-block;"></span>
        Не сделано
    </span>
    <span class="flex items-center gap-1.5">
        <span style="width:10px;height:10px;border-radius:3px;background:#F59E0B;display:inline-block;"></span>
        Пытался, не решил
    </span>
    <span class="flex items-center gap-1.5">
        <span style="width:10px;height:10px;border-radius:3px;background:#86EFAC;display:inline-block;"></span>
        Решено со 2-й попытки
    </span>
    <span class="flex items-center gap-1.5">
        <span style="width:10px;height:10px;border-radius:3px;background:#16A34A;display:inline-block;"></span>
        Решено с первого раза
    </span>
</div>