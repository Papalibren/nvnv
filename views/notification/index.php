<?php
/** @var app\models\Notification[] $notifications */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Уведомления';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Уведомления</h1>
    <?php if ($notifications): ?>
        <div class="flex gap-2">
            <a href="<?= Url::to(['/notifications/mark-all-read']) ?>" class="btn-secondary text-sm">
                Отметить всё прочитанным
            </a>
            <a href="<?= Url::to(['/notifications/clear-all']) ?>" class="btn-secondary text-sm text-acid-pink"
               onclick="return confirm('Удалить все уведомления?')">
                Очистить всё
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Уведомлений нет.</p></div>
<?php else: ?>
    <div class="space-y-2">
        <?php foreach ($notifications as $n): ?>
            <div class="<?= $n->is_read ? 'notification-read' : 'notification-unread' ?>">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-base-100"><?= Html::encode($n->title) ?></p>
                    <p class="text-sm text-base-400 mt-0.5"><?= Html::encode($n->body) ?></p>
                    <p class="text-xs text-base-400 mt-1"><?= Yii::$app->formatter->asDatetime($n->created_at, 'php:d.m.Y H:i') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>