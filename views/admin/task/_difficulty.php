<?php
/** @var app\models\Task $task */
?>
<div class="flex gap-0.5 items-center">
    <?php for ($i = 1; $i <= 10; $i++):
        if ($i <= $task->difficulty) {
            $color = match(true) {
                $task->difficulty <= 3 => '#22C55E',
                $task->difficulty <= 7 => '#3B82F6',
                default                => '#EF4444',
            };
        } else {
            $color = '#CBD5E1';
        }
    ?>
        <div class="w-1.5 h-3 rounded-sm"
             style="background: <?= $color ?>"></div>
    <?php endfor; ?>
    <span class="text-xs text-base-400 ml-1"><?= $task->difficulty ?></span>
</div>