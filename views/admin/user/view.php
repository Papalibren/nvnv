<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\User[] $teachers */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;

$this->title = $user->name;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/user/index']) ?>"
        class="hover:text-base-100 transition-colors duration-150 no-underline">
        Пользователи
    </a>
    <span>/</span>
    <span class="text-base-100"><?= Html::encode($user->name) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-6"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2 space-y-5">

        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">Информация</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-base-400">Имя</dt>
                    <dd class="text-base-100 font-medium"><?= Html::encode($user->name) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Роль</dt>
                    <dd class="text-base-100"><?= User::getLabel($user->role) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Логин</dt>
                    <dd class="text-base-100 font-mono"><?= $user->username ?: '—' ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Email</dt>
                    <dd class="text-base-100"><?= $user->email ?: '—' ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Статус</dt>
                    <dd><?= $this->render('_status_badge', ['user' => $user]) ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Режим обучения</dt>
                    <dd>
                        <?php if ($user->role === User::ROLE_STUDENT): ?>
                            <span class="<?= $user->isTutored() ? 'badge-violet' : 'badge-indigo' ?>">
                                <?= $user->isTutored() ? 'С репетитором' : 'Самостоятельно' ?>
                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-base-400">Регистрация</dt>
                    <dd class="text-base-100">
                        <?= Yii::$app->formatter->asDatetime($user->created_at, 'php:d.m.Y H:i') ?>
                    </dd>
                </div>
            </dl>
        </div>

        <?php if ($user->role === User::ROLE_STUDENT): ?>
            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-4">Баллы и активность</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg bg-base-900 text-center">
                        <p class="text-2xl font-bold text-acid-lime"><?= $user->getTotalPoints() ?></p>
                        <p class="text-xs text-base-400 mt-1">Всего баллов</p>
                    </div>
                    <div class="p-4 rounded-lg bg-base-900 text-center">
                        <p class="text-2xl font-bold text-base-100">
                            <?= $user->getGroups()->count() ?>
                        </p>
                        <p class="text-xs text-base-400 mt-1">Групп</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="space-y-5">

        <?php if ($user->role === User::ROLE_STUDENT): ?>
            <div class="card">
                <h2 class="text-base font-semibold text-base-100 mb-3">Привязка к учителю</h2>

                <?php
                $currentTeacher = $user->getTeacher()->one();
                ?>

                <form method="post" action="<?= Url::to(['/admin/user/assign-teacher', 'id' => $user->id]) ?>">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                    <div class="field-group">
                        <select name="teacher_id" class="input">
                            <option value="">Без учителя</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher->id ?>"
                                    <?= ($currentTeacher && $currentTeacher->id === $teacher->id) ? 'selected' : '' ?>>
                                    <?= Html::encode($teacher->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full text-sm py-2']) ?>
                </form>
            </div>
        <?php endif; ?>
<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-3">Часовой пояс</h2>
    <form method="post" action="<?= Url::to(['/admin/user/update-timezone', 'id' => $user->id]) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <div class="field-group">
            <select name="timezone" class="input">
                <?php foreach (User::timezoneList() as $tz => $label): ?>
                    <option value="<?= $tz ?>" <?= $user->timezone === $tz ? 'selected' : '' ?>>
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full text-sm py-2']) ?>
    </form>
</div>
    </div>

</div>

<?php if ($user->role === User::ROLE_STUDENT && $heatmapWeeks): ?>
<div class="card mt-6">
    <h2 class="text-base font-semibold text-base-100 mb-4">Активность</h2>
    <?= $this->render('@app/views/shared/_activity_heatmap', ['weeks' => $heatmapWeeks]) ?>
</div>
<?php endif; ?>