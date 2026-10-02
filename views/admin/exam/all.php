<?php
/** @var app\models\Exam[] $exams */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Все экзамены';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Все экзамены</h1>
    <div class="flex gap-2">
        <a href="<?= Url::to(['/admin/exam/index']) ?>" class="btn-secondary">Публичные экзамены</a>
        <a href="<?= Url::to(['/admin/exam/create']) ?>" class="btn-primary">+ Публичный экзамен</a>
    </div>
</div>

<?php if (empty($exams)): ?>
    <div class="card text-center py-16"><p class="text-base-400">Экзаменов нет.</p></div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Название</th>
                    <th style="width:140px">Автор</th>
                    <th style="width:100px">Публичный</th>
                    <th style="width:100px">Попыток</th>
                    <th style="width:100px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr id="exam-row-<?= $exam->id ?>">
                    <td class="font-medium text-base-100"><?= Html::encode($exam->title) ?></td>
                    <td class="text-sm text-base-400"><?= Html::encode($exam->teacher->name ?? '—') ?></td>
                    <td>
                        <?= $exam->is_public ? '<span class="badge-indigo">Да</span>' : '<span class="badge-gray">Нет</span>' ?>
                    </td>
                    <td class="text-sm text-base-400"><?= count($exam->attempts) ?></td>
                    <td>
                        <?php if (empty($exam->attempts)): ?>
                            <button class="btn-ghost text-xs text-acid-pink"
                                    hx-delete="<?= Url::to(['/admin/exam/delete', 'id' => $exam->id]) ?>"
                                    hx-target="#exam-row-<?= $exam->id ?>"
                                    hx-swap="outerHTML swap:300ms"
                                    hx-confirm="Удалить экзамен «<?= Html::encode(addslashes($exam->title)) ?>»?">
                                Удалить
                            </button>
                        <?php else: ?>
                            <span class="text-xs text-base-400">есть попытки</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>