<?php
/** @var app\models\Group $group */
/** @var app\models\User[] $available */
/** @var int[] $inGroup */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $group->name;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/teacher/group/index']) ?>" class="hover:text-base-100 no-underline">Группы</a>
    <span>/</span><span class="text-base-100"><?= Html::encode($group->name) ?></span>
    <span>·</span>
    <a href="<?= Url::to(['/teacher/group/timeline', 'id' => $group->id]) ?>" class="text-acid-lime no-underline">Таймлайн →</a>
</div>

<div class="grid grid-cols-3 gap-6">

    <!-- Ученики группы -->
    <div class="col-span-2 card">
        <h2 class="text-base font-semibold text-base-100 mb-4">
            Ученики (<?= count($inGroup) ?>)
        </h2>

        <?php $students = $group->students; ?>

        <?php if (empty($students)): ?>
            <p class="text-base-400 text-sm">В группе пока нет учеников.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($students as $student): ?>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-base-900">
                        <div>
                            <p class="text-sm font-medium text-base-100">
                                <?= Html::encode($student->name) ?>
                            </p>
                            <p class="text-xs text-base-400">
                                <?= $student->username ? '@' . Html::encode($student->username) : 'Логин не установлен' ?>
                            </p>
                        </div>
                        <form method="post" action="<?= Url::to(['/teacher/group/remove-student', 'id' => $group->id]) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <?= Html::hiddenInput('student_id', $student->id) ?>
                            <?= Html::submitButton('Удалить', ['class' => 'btn-ghost text-xs text-acid-pink',
                                'onclick' => 'return confirm("Удалить ученика из группы?")']) ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Добавить ученика -->
    <div class="card">
        <h2 class="text-base font-semibold text-base-100 mb-4">Добавить ученика</h2>

        <?php
        $notInGroup = array_filter($available, fn($s) => !in_array($s->id, $inGroup));
        ?>

        <?php if (empty($notInGroup)): ?>
            <p class="text-base-400 text-sm">Все ученики уже в группе.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($notInGroup as $student): ?>
                    <form method="post" action="<?= Url::to(['/teacher/group/add-student', 'id' => $group->id]) ?>">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <?= Html::hiddenInput('student_id', $student->id) ?>
                        <button type="submit"
                                class="w-full text-left p-2.5 rounded-lg bg-base-900 hover:bg-base-800
                                       transition-colors duration-150 text-sm text-base-100">
                            + <?= Html::encode($student->name) ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>