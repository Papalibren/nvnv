<?php

/** @var app\models\ClassSession $session */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ClassSession;

$this->title = $session->title;
$statusLabels = ClassSession::getStatusLabels();
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/schedule/index']) ?>" class="hover:text-base-100 no-underline">Расписание</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($session->title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-base-100"><?= Html::encode($session->title) ?></h1>
            <?php
            $teacherTz = new \DateTimeZone(Yii::$app->user->identity->timezone ?? 'Europe/Moscow');
            $teacherDt = new \DateTime('@' . $session->scheduled_at);
            $teacherDt->setTimezone($teacherTz);

            $studentUser = $session->student;
            ?>
            <p class="text-sm text-base-400 mt-1">
                <?= $session->student ? Html::encode($session->student->name) : Html::encode($session->group->name ?? '') ?>
            </p>
            <div class="flex items-center gap-4 mt-1.5">
                <span class="text-sm text-base-100 font-medium">
                    У вас: <?= $teacherDt->format('d.m.Y H:i') ?>
                </span>
                <?php if ($studentUser): ?>
                    <?php
                    $studentTz = new \DateTimeZone($studentUser->timezone ?: 'Europe/Moscow');
                    $studentDt = new \DateTime('@' . $session->scheduled_at);
                    $studentDt->setTimezone($studentTz);
                    ?>
                    <span class="text-sm text-base-400">
                        У ученика: <?= $studentDt->format('d.m.Y H:i') ?> (<?= $studentUser->getTimezoneOffsetLabel() ?>)
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($session->status === 'scheduled' && $session->isPast()): ?>
                <span class="badge-red">Просрочено</span>
            <?php else: ?>
                <span class="<?= $session->status === 'completed' ? 'badge-indigo' : ($session->status === 'cancelled' ? 'badge-red' : 'badge-gray') ?>">
                    <?= $statusLabels[$session->status] ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($session->lesson): ?>
        <div class="p-3 rounded-lg bg-base-900 mb-3">
            <p class="text-xs text-base-400 mb-1">Материал из банка уроков</p>
            <a href="<?= Url::to(['/teacher/lesson/view', 'id' => $session->lesson_id]) ?>" class="text-sm text-acid-lime no-underline">
                📖 <?= Html::encode($session->lesson->title) ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($session->notes): ?>
        <div class="p-3 rounded-lg bg-base-900 mb-3">
            <p class="text-xs text-base-400 mb-1">Заметки</p>
            <p class="text-sm text-base-100"><?= nl2br(Html::encode($session->notes)) ?></p>
        </div>
    <?php endif; ?>

    <div class="flex gap-2 flex-wrap mt-4">
        <a href="<?= Url::to(['/teacher/schedule/update', 'id' => $session->id]) ?>" class="btn-secondary text-sm">
            Изменить занятие
        </a>

        <?php if ($session->status === 'scheduled'): ?>
            <a href="<?= Url::to(['/teacher/schedule/complete', 'id' => $session->id]) ?>" class="btn-primary text-sm">
                Отметить как проведённое
            </a>
            <button type="button" onclick="document.getElementById('reschedule-form').classList.toggle('hidden')" class="btn-secondary text-sm">
                Перенести
            </button>
            <a href="<?= Url::to(['/teacher/schedule/cancel', 'id' => $session->id]) ?>" class="btn-secondary text-sm"
                onclick="return confirm('Отменить занятие?')">
                Отменить
            </a>
        <?php endif; ?>
    </div>

    <?php if ($session->status === 'scheduled'): ?>
        <?php
        $rescheduleDate = date('Y-m-d', $session->scheduled_at);
        $rescheduleTime = date('H:i', $session->scheduled_at);
        ?>
        <form id="reschedule-form" method="post" action="<?= Url::to(['/teacher/schedule/reschedule', 'id' => $session->id]) ?>"
            class="hidden mt-3 flex gap-2 items-end flex-wrap">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div>
                <label>Новая дата</label>
                <input type="date" name="scheduled_date" class="input" required value="<?= $rescheduleDate ?>">
            </div>
            <div>
                <label>Новое время</label>
                <select name="scheduled_time" class="input" required>
                    <?php for ($h = 7; $h <= 22; $h++): foreach (['00', '30'] as $m): ?>
                            <?php $val = sprintf('%02d:%s', $h, $m); ?>
                            <option value="<?= $val ?>" <?= $val === $rescheduleTime ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach;
                    endfor; ?>
                </select>
            </div>
            <?= Html::submitButton('Перенести', ['class' => 'btn-primary text-sm py-2 px-4']) ?>
        </form>
    <?php endif; ?>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="card">
        <h2 class="text-sm font-semibold text-base-100 mb-3">Домашнее задание</h2>
        <?php if ($session->homework): ?>
            <a href="<?= Url::to(['/teacher/homework/view', 'id' => $session->homework_id]) ?>" class="btn-secondary text-sm">
                Открыть «<?= Html::encode($session->homework->title) ?>»
            </a>
        <?php else: ?>
            <a href="<?= Url::to(['/teacher/homework/create', 'sessionId' => $session->id]) ?>" class="btn-primary text-sm">
                + Прикрепить ДЗ
            </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="text-sm font-semibold text-base-100 mb-3">Экзамен</h2>
        <?php if ($session->exam): ?>
            <a href="<?= Url::to(['/teacher/exam/view', 'id' => $session->exam_id]) ?>" class="btn-secondary text-sm">
                Открыть «<?= Html::encode($session->exam->title) ?>»
            </a>
        <?php else: ?>
            <a href="<?= Url::to(['/teacher/exam/create', 'sessionId' => $session->id]) ?>" class="btn-primary text-sm">
                + Прикрепить экзамен
            </a>
        <?php endif; ?>
    </div>
</div>
<?= $this->render('_activity_timeline', ['session' => $session]) ?>