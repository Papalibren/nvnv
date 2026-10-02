<?php
/** @var app\models\User[] $students */
/** @var app\models\Group[] $groups */
/** @var app\models\Lesson[] $lessons */
/** @var string|null $error */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новое занятие';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/schedule/index']) ?>" class="hover:text-base-100 no-underline">Расписание</a>
    <span>/</span><span class="text-base-100">Новое занятие</span>
</div>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<div class="max-w-lg">
    <div class="card">
        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="field-group">
                <label>Название занятия *</label>
                <input type="text" name="title" class="input" required autofocus placeholder="Например: Разбор сортировок">
            </div>

            <div class="field-group">
                <label>Кому назначено *</label>
                <div class="flex gap-2 mb-2">
                    <label class="flex-1">
                        <input type="radio" name="target_type" value="student" class="hidden peer" id="tt-student" checked onchange="toggleTarget()">
                        <span class="block text-center py-2 rounded-lg border cursor-pointer text-sm
                                     peer-checked:bg-acid-lime peer-checked:text-white peer-checked:border-acid-lime"
                              style="border-color:#E2E8F0;">Ученику</span>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="target_type" value="group" class="hidden peer" id="tt-group" onchange="toggleTarget()">
                        <span class="block text-center py-2 rounded-lg border cursor-pointer text-sm
                                     peer-checked:bg-acid-lime peer-checked:text-white peer-checked:border-acid-lime"
                              style="border-color:#E2E8F0;">Группе</span>
                    </label>
                </div>

                <div id="student-select">
                    <select name="student_id" class="input">
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s->id ?>"><?= Html::encode($s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="group-select" class="hidden">
                    <select name="group_id" class="input">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g->id ?>"><?= Html::encode($g->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-group">
                <label>Дата и время *</label>
                <input type="datetime-local" name="scheduled_at" class="input" required>
            </div>

            <div class="field-group">
                <label>Длительность (минут)</label>
                <input type="number" name="duration_minutes" class="input" value="60">
            </div>

            <div class="field-group">
                <label>Урок из банка (необязательно)</label>
                <select name="lesson_id" class="input">
                    <option value="">Без привязки к банку уроков</option>
                    <?php foreach ($lessons as $l): ?>
                        <option value="<?= $l->id ?>"><?= Html::encode($l->title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group mb-0">
                <label>Заметки (необязательно)</label>
                <textarea name="notes" class="input" rows="2"></textarea>
            </div>

            <?= Html::submitButton('Запланировать', ['class' => 'btn-primary w-full mt-4']) ?>
        </form>
    </div>
</div>

<script>
function toggleTarget() {
    const isGroup = document.getElementById('tt-group').checked;
    document.getElementById('student-select').classList.toggle('hidden', isGroup);
    document.getElementById('group-select').classList.toggle('hidden', !isGroup);
}
</script>