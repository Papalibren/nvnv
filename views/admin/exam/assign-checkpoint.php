<?php
/** @var app\models\CourseLesson[] $lessons */
/** @var app\models\Exam[] $publicExams */
use yii\helpers\Html;

$this->title = 'Контрольные точки курса';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Контрольные точки курса</h1>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<div class="space-y-3">
    <?php foreach ($lessons as $cl): ?>
        <div class="card">
            <form method="post" class="flex items-center gap-4">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <?= Html::hiddenInput('course_lesson_id', $cl->id) ?>

                <span class="flex-1 font-medium text-base-100">
                    <?= Html::encode($cl->lesson->title ?? '') ?>
                </span>

                <select name="exam_id" class="input" style="width:280px;" onchange="this.form.requestSubmit()">
                    <option value="">— Без контрольной точки —</option>
                    <?php foreach ($publicExams as $exam): ?>
                        <option value="<?= $exam->id ?>" <?= $cl->checkpoint_exam_id == $exam->id ? 'selected' : '' ?>>
                            <?= Html::encode($exam->title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endforeach; ?>
</div>