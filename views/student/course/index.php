<?php
/** @var app\models\CourseEnrollment[] $enrollments */
/** @var app\models\Course[] $availableCourses */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\StudentProgress;

$this->title = 'Мои курсы';
?>

<h1 class="text-2xl font-bold text-base-100 mb-6">Мои курсы</h1>

<?php if (!empty($enrollments)): ?>
<div class="space-y-3 mb-8">
    <?php foreach ($enrollments as $enrollment): ?>
        <?php $progress = StudentProgress::findOne([
            'student_id' => Yii::$app->user->id,
            'course_id'  => $enrollment->course_id,
        ]); ?>
        <a href="<?= Url::to(['/student/course/view', 'courseId' => $enrollment->course_id]) ?>"
           class="card-interactive flex items-center justify-between no-underline">
            <div>
                <p class="font-semibold text-base-100"><?= Html::encode($enrollment->course->title ?? '') ?></p>
                <p class="text-xs text-base-400 mt-0.5">Пройдено <?= $progress->percent ?? 0 ?>%</p>
            </div>
            <span class="badge-indigo">Открыть</span>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($availableCourses)): ?>
<h2 class="text-base font-semibold text-base-100 mb-3">Доступные курсы</h2>
<div class="grid grid-cols-2 gap-4">
    <?php foreach ($availableCourses as $course): ?>
        <div class="card">
            <h3 class="font-semibold text-base-100 mb-1"><?= Html::encode($course->title) ?></h3>
            <?php if ($course->description): ?>
                <p class="text-sm text-base-400 mb-3"><?= Html::encode($course->description) ?></p>
            <?php endif; ?>
            <a href="<?= Url::to(['/student/course/choose-duration', 'courseId' => $course->id]) ?>" class="btn-primary text-sm">
                Начать курс
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php elseif (empty($enrollments)): ?>
<div class="card text-center py-16">
    <p class="text-base-400">Курсы пока не созданы администратором.</p>
</div>
<?php endif; ?>