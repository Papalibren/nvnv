<?php
/** @var app\models\ClassSession $session */
use app\models\ActivityLog;
use app\models\HomeworkStudent;
use app\models\ExamAttempt;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$logs = ActivityLog::find()
    ->where(['entity_type' => 'class_session', 'entity_id' => $session->id])
    ->orderBy(['created_at' => SORT_DESC])
    ->all();

$labels = [
    'session_created'        => 'Занятие создано',
    'session_rescheduled'    => 'Занятие перенесено',
    'session_cancelled'      => 'Занятие отменено',
    'session_completed'      => 'Занятие отмечено проведённым',
    'session_lesson_changed' => 'Изменён привязанный урок',
];

// Кто причастен к занятию — один ученик или вся группа
$studentIds = [];
if ($session->student_id) {
    $studentIds[] = $session->student_id;
} elseif ($session->group_id && $session->group) {
    $studentIds = ArrayHelper::getColumn($session->group->students, 'id');
}

$homeworkRecords = [];
if ($session->homework_id && $studentIds) {
    $homeworkRecords = HomeworkStudent::find()
        ->where(['homework_id' => $session->homework_id, 'student_id' => $studentIds])
        ->with('student')
        ->all();
}

$examRecords = [];
if ($session->exam_id && $studentIds) {
    $examRecords = ExamAttempt::find()
        ->where(['exam_id' => $session->exam_id, 'student_id' => $studentIds])
        ->with('student')
        ->all();
}
?>

<div class="card mt-6">
    <h2 class="text-sm font-semibold text-base-100 mb-3">История занятия</h2>

    <?php if (empty($logs)): ?>
        <p class="text-sm text-base-400">Событий пока нет.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($logs as $log): ?>
                <?php
                $data = $log->data ? json_decode($log->data, true) : [];
                $extra = '';
                if ($log->action === 'session_rescheduled' && isset($data['from'], $data['to'])) {
                    $extra = Yii::$app->formatter->asDatetime($data['from'], 'php:d.m.Y H:i')
                           . ' → ' . Yii::$app->formatter->asDatetime($data['to'], 'php:d.m.Y H:i');
                }
                ?>
                <div class="flex items-start gap-3 text-sm">
                    <span class="w-2 h-2 rounded-full bg-acid-lime mt-1.5 shrink-0"></span>
                    <div>
                        <p class="text-base-100"><?= $labels[$log->action] ?? Html::encode($log->action) ?></p>
                        <?php if ($extra): ?><p class="text-xs text-base-400"><?= Html::encode($extra) ?></p><?php endif; ?>
                        <p class="text-xs text-base-400">
                            <?= $log->user ? Html::encode($log->user->name) : '—' ?> ·
                            <?= Yii::$app->formatter->asDatetime($log->created_at, 'php:d.m.Y H:i') ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($session->homework_id): ?>
<div class="card mt-4">
    <h2 class="text-sm font-semibold text-base-100 mb-3">Домашнее задание — статус сдачи</h2>

    <?php if (empty($homeworkRecords)): ?>
        <p class="text-sm text-base-400">Ещё не назначено ни одному ученику.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($homeworkRecords as $hs): ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-100"><?= Html::encode($hs->student->name ?? '—') ?></span>
                    <span class="<?= $hs->isSubmitted() ? 'badge-indigo' : 'badge-gray' ?>">
                        <?= HomeworkStudent::getLabels()[$hs->status] ?? $hs->status ?>
                        <?php if ($hs->submitted_at): ?>
                            · <?= Yii::$app->formatter->asDatetime($hs->submitted_at, 'php:d.m.Y H:i') ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($session->exam_id): ?>
<div class="card mt-4">
    <h2 class="text-sm font-semibold text-base-100 mb-3">Экзамен — статус прохождения</h2>

    <?php if (empty($examRecords)): ?>
        <p class="text-sm text-base-400">Ещё никто не начинал.</p>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($examRecords as $attempt): ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-base-100"><?= Html::encode($attempt->student->name ?? '—') ?></span>
                    <span class="<?= $attempt->isFinished() ? 'badge-indigo' : 'badge-gray' ?>">
                        <?= ExamAttempt::getLabels()[$attempt->status] ?? $attempt->status ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>