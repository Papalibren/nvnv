<?php
/** @var app\models\ClassSession $session */
/** @var app\models\Lesson[] $lessons */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Изменить занятие';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/schedule/view', 'id' => $session->id]) ?>" class="hover:text-base-100 no-underline">
        <?= Html::encode($session->title) ?>
    </a>
    <span>/</span><span class="text-base-100">Изменить</span>
</div>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<div class="max-w-lg">
    <div class="card">
        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="field-group">
                <label>Название занятия *</label>
                <input type="text" name="title" class="input" required value="<?= Html::encode($session->title) ?>">
            </div>

            <div class="field-group">
                <label>Дата *</label>
                <input type="date" name="scheduled_date" class="input" required>
            </div>
            <div class="field-group">
                <label>Время *</label>
                <select name="scheduled_time" class="input" required>
                    <?php for ($h = 7; $h <= 22; $h++): foreach (['00', '30'] as $m): ?>
                        <option value="<?= sprintf('%02d:%s', $h, $m) ?>"><?= sprintf('%02d:%s', $h, $m) ?></option>
                    <?php endforeach; endfor; ?>
                </select>
            </div>

            <div class="field-group">
                <label>Длительность (минут)</label>
                <input type="number" name="duration_minutes" class="input" value="<?= $session->duration_minutes ?: 60 ?>">
            </div>

            <div class="field-group">
                <label>Урок из банка</label>
                <select name="lesson_id" class="input">
                    <option value="">Без привязки к банку уроков</option>
                    <?php foreach ($lessons as $l): ?>
                        <option value="<?= $l->id ?>" <?= $session->lesson_id == $l->id ? 'selected' : '' ?>>
                            <?= Html::encode($l->title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group mb-0">
                <label>Заметки</label>
                <textarea name="notes" class="input" rows="2"><?= Html::encode($session->notes ?? '') ?></textarea>
            </div>

            <?= Html::submitButton('Сохранить', ['class' => 'btn-primary w-full mt-4']) ?>
        </form>
    </div>
</div>