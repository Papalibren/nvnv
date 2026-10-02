<?php
/** @var app\models\Exam[] $exams */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Экзамены';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Экзамены</h1>
    <a href="<?= Url::to(['/teacher/exam/create']) ?>" class="btn-primary">+ Новый экзамен</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($exams)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400 mb-4">Экзаменов пока нет.</p>
        <a href="<?= Url::to(['/teacher/exam/create']) ?>" class="btn-primary">Создать первый</a>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Название</th>
                    <th style="width:120px">Группа</th>
                    <th style="width:100px">Таймер</th>
                    <th style="width:110px">Режим</th>
                    <th style="width:100px">Попыток</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td class="font-medium text-base-100"><?= Html::encode($exam->title) ?></td>
                    <td class="text-sm text-base-400"><?= $exam->group ? Html::encode($exam->group->name) : '—' ?></td>
                    <td class="text-sm text-base-400">
                        <?= $exam->hasTimer() ? $exam->duration_minutes . ' мин' : 'Без таймера' ?>
                    </td>
                    <td>
                        <?php if ($exam->is_proctored): ?>
                            <span class="badge-violet">Защищённый</span>
                        <?php else: ?>
                            <span class="badge-gray">Обычный</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-sm text-base-400"><?= count($exam->attempts) ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="<?= Url::to(['/teacher/exam/view', 'id' => $exam->id]) ?>" class="btn-ghost text-xs">Открыть</a>
                            <a href="<?= Url::to(['/teacher/exam/update', 'id' => $exam->id]) ?>" class="btn-ghost text-xs">Изменить</a>
                            <?php if (empty($exam->attempts)): ?>
                                <button class="btn-ghost text-xs text-acid-pink"
                                        hx-delete="<?= Url::to(['/teacher/exam/delete', 'id' => $exam->id]) ?>"
                                        hx-target="closest tr"
                                        hx-swap="outerHTML swap:300ms"
                                        hx-confirm="Удалить экзамен?">
                                    Удалить
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>