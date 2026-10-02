<?php
/** @var app\models\PublicChallenge[] $challenges */
/** @var int $studentId */
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\ContentRenderer;
use app\assets\KatexAsset;

KatexAsset::register($this);
$this->title = 'Публичные задачи';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-base-100">Публичные задачи</h1>
    <p class="text-sm text-base-400 mt-0.5">Новые задачи открываются несколько раз в неделю — решайте для рейтинга</p>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert-error mb-4"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
<?php endif; ?>

<?php if (empty($challenges)): ?>
    <div class="card text-center py-16">
        <p class="text-base-400">Пока нет открытых задач.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($challenges as $challenge): ?>
            <?php $attempt = $challenge->getAttemptFor($studentId); ?>
            <div class="card <?= !$challenge->isOpen() && !$attempt ? 'opacity-50' : '' ?>">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-base-100"><?= Html::encode($challenge->title) ?></h3>
                    <span class="badge-indigo"><?= $challenge->points ?> баллов</span>
                </div>

                <div class="prose-task text-sm mb-4">
                    <?= ContentRenderer::render($challenge->task->content) ?>
                </div>

                <?php if ($attempt): ?>
                    <div class="p-3 rounded-lg <?= $attempt->is_correct ? 'bg-acid-lime/10' : 'bg-acid-pink/10' ?> text-sm">
                        Ваш ответ: <code><?= Html::encode($attempt->answer_text) ?></code>
                        — <?= $attempt->is_correct ? 'верно ✓' : 'неверно ✗' ?>
                        (+<?= $attempt->points_earned ?> баллов)
                    </div>
                <?php elseif ($challenge->isOpen()): ?>
                    <form method="post" action="<?= Url::to(['/student/challenge/submit', 'id' => $challenge->id]) ?>"
                          class="flex gap-2">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <input type="text" name="answer" class="input font-mono" placeholder="Ваш ответ" required>
                        <button type="submit" class="btn-primary px-6 shrink-0">Ответить</button>
                    </form>
                <?php elseif ($challenge->isUpcoming()): ?>
                    <p class="text-sm text-base-400">
                        Откроется <?= Yii::$app->formatter->asDatetime($challenge->opens_at, 'php:d.m.Y H:i') ?>
                    </p>
                <?php else: ?>
                    <p class="text-sm text-base-400">Задача закрыта, ответ не был дан.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>