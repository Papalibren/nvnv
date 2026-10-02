<?php
/** @var app\models\ClassSession[] $sessions */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ClassSession;

$this->title = 'Мои занятия';
$statusLabels = ClassSession::getStatusLabels();
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Мои занятия</h1>

<?php if (empty($sessions)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Занятий пока не запланировано.</p></div>
<?php else: ?>
    <div class="space-y-2">
        <?php foreach ($sessions as $s): ?>
            <a href="<?= Url::to(['/student/schedule/view', 'id' => $s->id]) ?>"
               class="card flex items-center justify-between no-underline hover:border-acid-lime/40 transition-colors duration-150">
                <div>
                    <p class="font-medium text-base-100"><?= Html::encode($s->title) ?></p>
                    <p class="text-xs text-base-400 mt-0.5">
                        <?= Yii::$app->formatter->asDatetime($s->scheduled_at, 'php:d.m.Y H:i') ?>
                        <?php if ($s->duration_minutes): ?> · <?= $s->duration_minutes ?> мин<?php endif; ?>
                    </p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <?php if ($s->lesson): ?>
                            <span class="text-xs text-acid-lime">📖 теория</span>
                        <?php endif; ?>
                        <?php if ($s->homework_id): ?>
                            <span class="text-xs <?= $s->status === 'completed' ? 'text-acid-cyan' : 'text-base-400' ?>">
                                📝 ДЗ<?= $s->status !== 'completed' ? ' (после занятия)' : '' ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($s->exam_id): ?>
                            <span class="text-xs <?= $s->status === 'completed' ? 'text-acid-violet' : 'text-base-400' ?>">
                                🎯 экзамен<?= $s->status !== 'completed' ? ' (после занятия)' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="<?= $s->status === 'completed' ? 'badge-indigo' : ($s->status === 'cancelled' ? 'badge-red' : 'badge-gray') ?> shrink-0">
                    <?= $statusLabels[$s->status] ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>