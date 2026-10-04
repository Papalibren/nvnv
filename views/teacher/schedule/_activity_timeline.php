<?php
/** @var app\models\ClassSession $session */
use app\models\ActivityLog;
use yii\helpers\Html;

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