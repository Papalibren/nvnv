<?php
/** @var app\models\Notification[] $notifications */
use yii\helpers\Html;
?>

<div class="fixed right-4 top-16 z-50 w-80 bg-white rounded-xl shadow-card-hover"
     style="border: 1px solid #E2E8F0;"
     id="notif-dropdown">

    <div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid #E2E8F0;">
        <h3 class="text-sm font-semibold text-base-100">Уведомления</h3>
        <button onclick="document.getElementById('notif-dropdown').remove()" class="btn-ghost text-xs">✕</button>
    </div>

    <div class="max-h-80 overflow-y-auto">
        <?php if (empty($notifications)): ?>
            <p class="text-base-400 text-sm text-center py-8">Нет уведомлений</p>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="<?= $n->is_read ? 'notification-read' : 'notification-unread' ?> mb-1 mx-2 mt-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-base-100 truncate"><?= Html::encode($n->title) ?></p>
                        <p class="text-xs text-base-400 mt-0.5"><?= Html::encode($n->body) ?></p>
                        <p class="text-xs text-base-400 mt-1"><?= Yii::$app->formatter->asRelativeTime($n->created_at) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-between px-4 py-2.5" style="border-top: 1px solid #E2E8F0;">
        <button hx-post="/notifications/mark-all-read" hx-target="#notif-dropdown" hx-swap="outerHTML"
                class="text-xs text-acid-lime hover:text-acid-violet">
            Отметить всё прочитанным
        </button>
        <a href="/notifications" class="text-xs text-base-400 hover:text-base-100 no-underline">
            Все уведомления →
        </a>
    </div>
</div>