<?php
/** @var app\models\Lesson|null $lesson */
/** @var app\models\LessonTopic[] $topics */
/** @var app\models\Group[] $groups */
/** @var app\models\BookPage[] $bookPages */
/** @var int[]|null $selectedPageIds */
/** @var int|null $topicId */
/** @var string|null $error */
/** @var bool $isNew */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $isNew ? 'Новый урок' : 'Изменить урок';
$selectedPageIds = $selectedPageIds ?? [];
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/lesson/index']) ?>" class="hover:text-base-100 no-underline">Уроки</a>
    <span>/</span><span class="text-base-100"><?= $isNew ? 'Новый урок' : Html::encode($lesson->title) ?></span>
</div>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 320px;">

        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-3">Тип урока</h2>
            <div class="flex gap-2 mb-4">
                <label class="flex-1">
                    <input type="radio" name="lesson_type" value="practice" class="hidden peer" id="type-practice"
                           <?= (!$lesson || $lesson->lesson_type !== 'info') ? 'checked' : '' ?> onchange="toggleType()">
                    <span class="block text-center py-2.5 rounded-lg border cursor-pointer text-sm font-medium
                                 peer-checked:bg-acid-lime peer-checked:text-white peer-checked:border-acid-lime"
                          style="border-color:#E2E8F0;">📝 Практика (с теорией)</span>
                </label>
                <label class="flex-1">
                    <input type="radio" name="lesson_type" value="info" class="hidden peer" id="type-info"
                           <?= ($lesson && $lesson->lesson_type === 'info') ? 'checked' : '' ?> onchange="toggleType()">
                    <span class="block text-center py-2.5 rounded-lg border cursor-pointer text-sm font-medium
                                 peer-checked:bg-acid-violet peer-checked:text-white peer-checked:border-acid-violet"
                          style="border-color:#E2E8F0;">ℹ️ Информационный</span>
                </label>
            </div>

            <div id="practice-block">
                <p class="text-sm text-base-400 mb-2">Связанные страницы учебника (необязательно):</p>
                <?php
                $grouped = [];
                foreach ($bookPages as $p) { $grouped[$p->chapter->section->title ?? '—'][] = $p; }
                ?>
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    <?php foreach ($grouped as $sectionTitle => $pages): ?>
                        <div>
                            <p class="text-xs font-semibold text-base-400 uppercase tracking-wide mb-1">
                                <?= Html::encode($sectionTitle) ?>
                            </p>
                            <?php foreach ($pages as $page): ?>
                                <label class="flex items-center gap-2 text-sm cursor-pointer py-0.5">
                                    <?= Html::checkbox('book_page_ids[]', in_array($page->id, $selectedPageIds), [
                                        'value' => $page->id, 'class' => 'accent-acid-lime',
                                    ]) ?>
                                    <span class="text-base-400"><?= Html::encode($page->title) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="info-block" class="hidden">
                <label>Текст урока (Markdown)</label>
                <textarea name="info_content" rows="10" class="input font-mono text-sm"><?= $lesson && $lesson->lesson_type === 'info' ? Html::encode($lesson->info_content ?? '') : '' ?></textarea>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card">
                <div class="field-group">
                    <label>Название урока *</label>
                    <input type="text" name="title" class="input" required autofocus
                           value="<?= $isNew ? '' : Html::encode($lesson->title) ?>">
                </div>
                <div class="field-group">
                    <label>Тема</label>
                    <select name="topic_id" class="input">
                        <option value="">Без темы</option>
                        <?php foreach ($topics as $topic): ?>
                            <?php $selected = $isNew ? $topicId == $topic->id : $lesson->topic_id == $topic->id; ?>
                            <option value="<?= $topic->id ?>" <?= $selected ? 'selected' : '' ?>><?= Html::encode($topic->title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-group mb-0">
                    <label>Группа (необязательно)</label>
                    <select name="group_id" class="input">
                        <option value="">Без группы</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group->id ?>" <?= (!$isNew && $lesson->group_id == $group->id) ? 'selected' : '' ?>>
                                <?= Html::encode($group->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?= Html::submitButton($isNew ? 'Создать урок' : 'Сохранить', ['class' => 'btn-primary w-full']) ?>
        </div>
    </div>
</form>

<script>
function toggleType() {
    const isInfo = document.getElementById('type-info').checked;
    document.getElementById('practice-block').classList.toggle('hidden', isInfo);
    document.getElementById('info-block').classList.toggle('hidden', !isInfo);
}
toggleType();
</script>