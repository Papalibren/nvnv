<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\Icons;

$user  = Yii::$app->user->identity;
$role  = $user->role;

$navMap = [
    'admin'   => '@app/views/partials/nav/admin.php',
    'teacher' => '@app/views/partials/nav/teacher.php',
    'student' => '@app/views/partials/nav/student.php',
];

$items = require Yii::getAlias($navMap[$role] ?? $navMap['student']);

if ($role === 'student') {
    $featuresOn = Yii::$app->params['features'] ?? [];

    $hiddenLabels = [];

    if ($user->isSelfStudy()) {
        // Раньше self-study видел только "Мой курс" — теперь курсы выключены совсем
        $hiddenLabels[] = 'Домашние задания';
        $hiddenLabels[] = 'Экзамены';
    } else {
        $hiddenLabels[] = 'Мой курс';
    }

    if (empty($featuresOn['courses'])) {
        $hiddenLabels[] = 'Мой курс';
    }
    if (empty($featuresOn['publicChallenges'])) {
        $hiddenLabels[] = 'Публичные задачи';
    }

    $items = array_filter($items, fn($item) => !in_array($item['label'], $hiddenLabels));
}

// Текущий маршрут вида "admin/task/index"
$currentRoute = Yii::$app->controller->module->id !== Yii::$app->id
    ? Yii::$app->controller->id
    : Yii::$app->controller->id;
$currentController = Yii::$app->controller->id; // например 'task' или 'dashboard'
?>

<aside class="w-60 min-h-screen flex flex-col shrink-0 bg-white"
       style="border-right: 1px solid #D0D7E3;">

    <div class="px-6 py-4" style="border-bottom: 1px solid #D0D7E3;">
        <a href="/" class="block no-underline">
            <span class="text-lg font-bold grad-text">ЕГЭ Инфо</span>
        </a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <?php foreach ($items as $item): ?>
            <?php
            $itemUrl  = $item['url'][0]; // например '/admin/task/index'
            $parts    = explode('/', trim($itemUrl, '/')); // ['admin','task','index']
            $itemController = $parts[1] ?? '';

            $isActive = $currentController === $itemController;
            $class    = $isActive ? 'sidebar-item-active' : 'sidebar-item';
            ?>
            <a href="<?= Url::to($item['url']) ?>" class="<?= $class ?> no-underline">
                <?= Icons::get($item['icon']) ?>
                <span><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($role === 'student' && $user->isSelfStudy()): ?>
    <div class="mx-3 mb-3 p-3 rounded-lg"
         style="background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(124,58,237,0.08));
                border: 1px solid rgba(79,70,229,0.15);">
        <p class="text-xs font-semibold text-base-100 mb-1">
            Нужна помощь репетитора?
        </p>
        <p class="text-xs text-base-400 mb-2 leading-snug">
            Персональные занятия ускорят подготовку и разберут ваши ошибки.
        </p>
        <a href="/zapros-repetitora"
           class="btn-primary text-xs py-1.5 px-3 w-full text-center block no-underline">
            Узнать подробнее
        </a>
    </div>
<?php endif; ?>

    <div class="px-3 py-4 space-y-0.5" style="border-top: 1px solid #D0D7E3;">
        <div class="px-4 py-2 mb-1">
            <p class="text-xs text-base-400 mb-0.5">
                <?= Html::encode(\app\models\User::getLabel($role)) ?>
            </p>
            <p class="text-sm text-base-100 font-semibold truncate">
                <?= Html::encode($user->name) ?>
            </p>
        </div>
        <a href="/logout"
           class="sidebar-item text-acid-pink hover:text-acid-pink hover:bg-acid-pink/10 no-underline">
            <?= Icons::get('logout') ?>
            <span>Выйти</span>
        </a>
    </div>

</aside>