<?php

use yii\helpers\Html;
use app\helpers\Icons;
use app\models\Notification;

$user        = Yii::$app->user->identity;
$unreadCount = Notification::countUnread($user->id);
?>

<header class="h-14 flex items-center justify-between px-6 shrink-0 bg-white"
    style="border-bottom: 1px solid #D0D7E3;">

    <h1 class="text-base font-semibold text-base-100">
        <?= Html::encode($this->title ?? '') ?>
    </h1>

    <div class="flex items-center gap-4">
        <button id="notif-btn"
            class="relative text-base-400 hover:text-base-100 transition-colors duration-150"
            hx-get="/notifications/panel"
            hx-target="body"
            hx-swap="beforeend"
            hx-on::before-request="document.getElementById('notif-dropdown')?.remove()">
            <?= Icons::get('bell') ?>
            <span id="notif-count"
                hx-get="/notifications/count"
                hx-trigger="load, every 30s"
                hx-target="#notif-count"
                hx-swap="innerHTML"></span>
        </button>

        <span class="text-sm text-base-400">
            <?= Html::encode($user->name) ?>
        </span>
    </div>
</header>