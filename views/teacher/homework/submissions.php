<?php

/** @var app\models\Homework $homework */
/** @var app\models\HomeworkStudent[] $submissions */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\HomeworkStudent;

$this->title = 'Проверка: ' . $homework->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/homework/view', 'id' => $homework->id]) ?>" class="hover:text-base-100 no-underline">
        <?= Html::encode($homework->title) ?>
    </a>
    <span>/</span><span class="text-base-100">Проверка</span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($submissions)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400">Пока никто не сдал.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($submissions as $hs): ?>
            <div class="card <?= $hs->status === HomeworkStudent::STATUS_REVIEWED ? 'opacity-70' : '' ?>">
                <form method="post" action="<?= Url::to(['/teacher/homework/review', 'id' => $hs->id]) ?>">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold text-base-100"><?= Html::encode($hs->student->name) ?></p>
                            <p class="text-xs text-base-400">
                                Сдано: <?= Yii::$app->formatter->asDatetime($hs->submitted_at, 'php:d.m.Y H:i') ?>
                                <?= $hs->wasSubmittedLate() ? ' (после дедлайна)' : '' ?>
                            </p>
                        </div>
                        <span class="<?= $hs->status === HomeworkStudent::STATUS_REVIEWED ? 'badge-indigo' : 'badge-gray' ?>">
                            <?= HomeworkStudent::getLabel($hs->status) ?>
                        </span>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($hs->answers as $answer): ?>
                            <div class="p-3 rounded-lg bg-base-900">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-base-400">
                                        Задание <?= $answer->homeworkTask->task->task_number ?? '—' ?>
                                        <?= $answer->attempt_number > 1 ? ' (2-я попытка)' : '' ?>
                                    </span>
                                    <?php if ($answer->needs_manual_review): ?>
                                        <span class="text-acid-violet text-sm font-semibold">● требует проверки</span>
                                    <?php else: ?>
                                        <span class="<?= $answer->is_correct ? 'text-acid-lime' : 'text-acid-pink' ?> text-sm font-semibold">
                                            <?= $answer->is_correct ? '✓ верно' : '✗ неверно' ?> · +<?= $answer->points_earned ?> б.
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm font-mono text-base-100 mb-1">
                                    Ответ: <?= Html::encode($answer->answer_text ?: '—') ?>
                                </p>
                                <?php if ($answer->file_path): ?>
                                    <a href="/files/<?= $answer->file_path ?>" target="_blank"
                                        class="text-xs text-acid-cyan no-underline">📎 Прикреплённый файл</a>
                                <?php endif; ?>

                                <textarea name="comment[<?= $answer->id ?>]" rows="2"
                                    placeholder="Комментарий ученику (необязательно)"
                                    class="input text-sm mt-2"><?= Html::encode($answer->teacher_comment ?? '') ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3">
                        <?= Html::submitButton(
                            $hs->status === HomeworkStudent::STATUS_REVIEWED ? 'Обновить проверку' : 'Отметить проверенным',
                            ['class' => 'btn-primary text-sm py-1.5 px-4']
                        ) ?>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>