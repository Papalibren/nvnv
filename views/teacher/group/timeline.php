<?php
/** @var app\models\Group $group */
/** @var app\models\ClassSession[] $sessions */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ClassSession;
use app\models\HomeworkStudent;

$this->title = 'Таймлайн: ' . $group->name;
$statusLabels = ClassSession::getStatusLabels();
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/group/view', 'id' => $group->id]) ?>" class="hover:text-base-100 no-underline">
        <?= Html::encode($group->name) ?>
    </a>
    <span>/</span><span class="text-base-100">Таймлайн</span>
</div>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Таймлайн группы «<?= Html::encode($group->name) ?>»</h1>
    <a href="<?= Url::to(['/teacher/schedule/create']) ?>" class="btn-primary text-sm">+ Занятие</a>
</div>

<?php if (empty($sessions)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Занятий пока не было.</p></div>
<?php else: ?>
    <div class="relative pl-6" style="border-left: 2px solid #E2E8F0;">
        <?php foreach ($sessions as $s): ?>
            <?php
            $submittedCount = 0;
            $totalCount = 0;
            if ($s->homework_id) {
                $totalCount = HomeworkStudent::find()->where(['homework_id' => $s->homework_id])->count();
                $submittedCount = HomeworkStudent::find()
                    ->where(['homework_id' => $s->homework_id])
                    ->andWhere(['in', 'status', ['submitted', 'reviewed']])
                    ->count();
            }
            ?>
            <div class="relative mb-5">
                <span class="absolute rounded-full"
                      style="left: -30px; top: 4px; width: 12px; height: 12px;
                             background: <?= $s->status === 'completed' ? '#4F46E5' : ($s->status === 'cancelled' ? '#E11D48' : '#CBD5E1') ?>;
                             border: 2px solid white;"></span>

                <div class="card">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-semibold text-base-100"><?= Html::encode($s->title) ?></p>
                            <p class="text-xs text-base-400 mt-0.5">
                                <?= Yii::$app->formatter->asDatetime($s->scheduled_at, 'php:d.m.Y H:i') ?>
                            </p>
                        </div>
                        <span class="<?= $s->status === 'completed' ? 'badge-indigo' : ($s->status === 'cancelled' ? 'badge-red' : 'badge-gray') ?>">
                            <?= $statusLabels[$s->status] ?>
                        </span>
                    </div>

                    <div class="flex items-center gap-4 mt-3 flex-wrap">
                        <?php if ($s->lesson): ?>
                            <a href="<?= Url::to(['/teacher/lesson/view', 'id' => $s->lesson_id]) ?>" class="text-xs text-acid-lime no-underline">
                                📖 <?= Html::encode($s->lesson->title) ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($s->homework_id): ?>
                            <a href="<?= Url::to(['/teacher/homework/submissions', 'id' => $s->homework_id]) ?>" class="text-xs text-acid-cyan no-underline">
                                📝 ДЗ: <?= $submittedCount ?>/<?= $totalCount ?> сдали
                            </a>
                        <?php endif; ?>

                        <?php if ($s->exam_id): ?>
                            <a href="<?= Url::to(['/teacher/exam/view', 'id' => $s->exam_id]) ?>" class="text-xs text-acid-violet no-underline">
                                🎯 <?= Html::encode($s->exam->title ?? '') ?>
                            </a>
                        <?php endif; ?>

                        <a href="<?= Url::to(['/teacher/schedule/view', 'id' => $s->id]) ?>" class="text-xs text-base-400 no-underline ml-auto">
                            Открыть →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>