<?php
/** @var app\models\Course $course */
/** @var app\models\CourseLesson|null $lesson */
/** @var app\models\BookPage[] $bookPages */
/** @var int[] $selectedPageIds */
/** @var string|null $error */
/** @var bool $isNew */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $isNew ? 'Новая тема' : 'Изменить тему';
$selectedPageIds = $selectedPageIds ?? [];
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/course/view', 'id' => $course->id]) ?>" class="hover:text-base-100 no-underline">
        <?= Html::encode($course->title) ?>
    </a>
    <span>/</span><span class="text-base-100"><?= $isNew ? 'Новая тема' : Html::encode($lesson->lesson->title ?? '') ?></span>
</div>

<?php if ($error): ?><div class="alert-error mb-4"><?= Html::encode($error) ?></div><?php endif; ?>

<form method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 320px;">

<div class="card">
    <h2 class="text-base font-semibold text-base-100 mb-4">Тип темы</h2>

    <div class="flex gap-2 mb-4">
        <label class="flex-1">
            <input type="radio" name="lesson_type" value="practice" class="hidden peer" id="type-practice"
                   <?= (!$lesson || $lesson->lesson->lesson_type !== 'info') ? 'checked' : '' ?>
                   onchange="toggleLessonType()">
            <span class="block text-center py-2.5 rounded-lg border cursor-pointer text-sm font-medium
                         peer-checked:bg-acid-lime peer-checked:text-white peer-checked:border-acid-lime"
                  style="border-color:#E2E8F0;">
                📝 Практика (теория + задачи)
            </span>
        </label>
        <label class="flex-1">
            <input type="radio" name="lesson_type" value="info" class="hidden peer" id="type-info"
                   <?= ($lesson && $lesson->lesson->lesson_type === 'info') ? 'checked' : '' ?>
                   onchange="toggleLessonType()">
            <span class="block text-center py-2.5 rounded-lg border cursor-pointer text-sm font-medium
                         peer-checked:bg-acid-violet peer-checked:text-white peer-checked:border-acid-violet"
                  style="border-color:#E2E8F0;">
                ℹ️ Информационная (просто текст)
            </span>
        </label>
    </div>

    <!-- Блок практики -->
    <div id="practice-block">
        <p class="text-sm text-base-400 mb-3">
            Выберите страницы учебника — по ним автоматически подберутся связанные задачи.
        </p>
        <?php
        $grouped = [];
        foreach ($bookPages as $p) {
            $grouped[$p->chapter->section->title ?? '—'][] = $p;
        }
        $selectedPageIds = $selectedPageIds ?? [];
        ?>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php foreach ($grouped as $sectionTitle => $pages): ?>
                <div>
                    <p class="text-xs font-semibold text-base-400 uppercase tracking-wide mb-1">
                        <?= Html::encode($sectionTitle) ?>
                    </p>
                    <?php foreach ($pages as $page): ?>
                        <label class="flex items-center gap-2 text-sm cursor-pointer py-0.5">
                            <?= Html::checkbox('book_page_ids[]', in_array($page->id, $selectedPageIds), [
                                'value' => $page->id,
                                'class' => 'accent-acid-lime',
                            ]) ?>
                            <span class="text-base-400"><?= Html::encode($page->title) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Блок информационного текста -->
    <div id="info-block" class="hidden">
        <label>Текст темы (Markdown)</label>
        <textarea name="info_content" rows="14" class="input font-mono text-sm"
                  placeholder="Расскажите ученику о формате экзамена, структуре заданий и т.д."><?= $lesson && $lesson->lesson->lesson_type === 'info' ? Html::encode($lesson->lesson->info_content ?? '') : '' ?></textarea>
        <p class="text-xs text-base-400 mt-1.5">
            Ученик сам отметит тему как прочитанную — баллы за неё не начисляются, но она нужна для открытия следующих тем.
        </p>
    </div>
</div>

<script>
function toggleLessonType() {
    const isInfo = document.getElementById('type-info').checked;
    document.getElementById('practice-block').classList.toggle('hidden', isInfo);
    document.getElementById('info-block').classList.toggle('hidden', !isInfo);
}
toggleLessonType();
</script>

        <div class="space-y-4">
            <div class="card">
                <div class="field-group">
                    <label>Название темы *</label>
                    <input type="text" name="title" class="input" required autofocus
                           value="<?= $isNew ? '' : Html::encode($lesson->lesson->title ?? '') ?>">
                </div>
            </div>

            <?= Html::submitButton($isNew ? 'Добавить тему' : 'Сохранить', ['class' => 'btn-primary w-full']) ?>
        </div>
    </div>
</form>