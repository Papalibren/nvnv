<?php
/** @var app\models\User $user */
use yii\helpers\Url;
use app\models\User;
?>
<span hx-get="<?= Url::to(['/admin/user/toggle-status', 'id' => $user->id]) ?>"
      hx-target="this"
      hx-swap="outerHTML"
      class="<?= $user->status === User::STATUS_ACTIVE ? 'badge-indigo' : 'badge-gray' ?> cursor-pointer"
      title="Нажмите чтобы изменить">
    <?= $user->status === User::STATUS_ACTIVE ? 'Активен'
        : ($user->status === User::STATUS_PENDING ? 'Ожидает' : 'Заблокирован') ?>
</span>