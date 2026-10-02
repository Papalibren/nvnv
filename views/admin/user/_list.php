<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;

$users = $dataProvider->getModels();
?>

<?php if (empty($users)): ?>
    <div class="card text-center py-12">
        <p class="text-base-400">Пользователи не найдены.</p>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Имя</th>
                    <th style="width:120px">Роль</th>
                    <th style="width:140px">Логин</th>
                    <th style="width:120px">Статус</th>
                    <th style="width:140px">Регистрация</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="font-medium text-base-100">
                        <?= Html::encode($user->name) ?>
                    </td>
                    <td>
                        <?php $roleColors = [
                            'admin'   => 'badge-red',
                            'teacher' => 'badge-violet',
                            'student' => 'badge-indigo',
                        ]; ?>
                        <span class="<?= $roleColors[$user->role] ?>">
                            <?= User::getLabel($user->role) ?>
                        </span>
                    </td>
                    <td class="text-base-400 font-mono text-xs">
                        <?= $user->username ? Html::encode($user->username) : '—' ?>
                    </td>
                    <td>
                        <?= $this->render('_status_badge', ['user' => $user]) ?>
                    </td>
                    <td class="text-base-400 text-xs">
                        <?= Yii::$app->formatter->asDate($user->created_at, 'dd.MM.yyyy') ?>
                    </td>
                    <td>
                        <a href="<?= Url::to(['/admin/user/view', 'id' => $user->id]) ?>"
                           class="btn-ghost text-xs">
                            Открыть
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>