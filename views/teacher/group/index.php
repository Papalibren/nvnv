<?php
/** @var app\models\Group[] $groups */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Группы';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-100">Группы</h1>
    <a href="<?= Url::to(['/teacher/group/create']) ?>" class="btn-primary">+ Новая группа</a>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if (empty($groups)): ?>
    <div class="card text-center py-12">
        <p class="text-base-400 mb-4">Групп пока нет.</p>
        <a href="<?= Url::to(['/teacher/group/create']) ?>" class="btn-primary">Создать группу</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-2 gap-4">
        <?php foreach ($groups as $group): ?>
            <a href="<?= Url::to(['/teacher/group/view', 'id' => $group->id]) ?>"
               class="card-interactive no-underline">
                <h3 class="font-semibold text-base-100 mb-1"><?= Html::encode($group->name) ?></h3>
                <?php if ($group->description): ?>
                    <p class="text-sm text-base-400 mb-3"><?= Html::encode($group->description) ?></p>
                <?php endif; ?>
                <span class="badge-gray"><?= $group->getStudentCount() ?> учеников</span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>