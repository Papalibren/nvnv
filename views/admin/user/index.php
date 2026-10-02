<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use yii\helpers\Url;

$this->title = 'Пользователи';
$currentRole = Yii::$app->request->get('role', '');
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-100">Пользователи</h1>
        <p class="text-sm text-base-400 mt-0.5">Всего: <?= $dataProvider->totalCount ?></p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= Url::to(['/admin/user/create-teacher']) ?>" class="btn-secondary">
            + Учитель
        </a>
        <a href="<?= Url::to(['/admin/user/invite-student']) ?>" class="btn-primary">
            + Пригласить ученика
        </a>
    </div>
</div>

<!-- Фильтр по роли -->
<div class="flex gap-2 mb-6">
    <a href="<?= Url::to(['/admin/user/index']) ?>"
       class="<?= $currentRole === '' ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
        Все
    </a>
    <a href="<?= Url::to(['/admin/user/index', 'role' => 'student']) ?>"
       class="<?= $currentRole === 'student' ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
        Ученики
    </a>
    <a href="<?= Url::to(['/admin/user/index', 'role' => 'teacher']) ?>"
       class="<?= $currentRole === 'teacher' ? 'btn-primary' : 'btn-secondary' ?> text-xs py-1.5 px-3 no-underline">
        Учителя
    </a>
</div>

<div id="user-list">
    <?= $this->render('_list', ['dataProvider' => $dataProvider]) ?>
</div>