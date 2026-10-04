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
            <p class="text-sm text-base-400 mt-1">
                <?= $session->student ? Html::encode($session->student->name) : Html::encode($session->group->name ?? '') ?>
                · <?= Yii::$app->formatter->asDatetime($session->scheduled_at, 'php:d.m.Y H:i') ?>
            </p>
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
    <form id="reschedule-form" method="post" action="<?= Url::to(['/teacher/schedule/reschedule', 'id' => $session->id]) ?>"
          class="hidden mt-3 flex gap-2 items-end">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <div class="flex-1">
            <label>Новая дата и время</label>
            <input type="datetime-local" name="scheduled_at" class="input" required
                   value="<?= date('Y-m-d\TH:i', $session->scheduled_at) ?>">
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