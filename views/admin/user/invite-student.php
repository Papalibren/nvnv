<?php
/** @var yii\web\View $this */
/** @var string $name */
/** @var string|null $error */
/** @var string|null $inviteUrl */
/** @var string|null $inviteName */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Пригласить ученика';
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/user/index']) ?>"
       class="hover:text-base-100 transition-colors duration-150 no-underline">
        Пользователи
    </a>
    <span>/</span>
    <span class="text-base-100">Пригласить ученика</span>
</div>

<div class="max-w-lg">

    <?php if ($inviteUrl): ?>
        <div class="card mb-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-acid-lime/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-acid-lime" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-base-100">
                        Ученик «<?= Html::encode($inviteName) ?>» создан
                    </h3>
                    <p class="text-sm text-base-400 mt-0.5">
                        Отправьте ему эту ссылку. Действительна 7 дней.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 rounded-lg bg-base-900">
                <input type="text" readonly value="<?= Html::encode($inviteUrl) ?>"
                       id="invite-url-input"
                       class="flex-1 bg-transparent text-sm font-mono text-base-100 outline-none">
                <button type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('invite-url-input').value); this.textContent='Скопировано!'; setTimeout(() => this.textContent='Копировать', 1500)"
                        class="btn-secondary text-xs py-1.5 px-3 shrink-0">
                    Копировать
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 class="text-base font-semibold text-base-100 mb-4">Новый ученик</h2>

        <?php if ($error): ?>
            <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

            <div class="field-group">
                <label>Имя ученика</label>
                <input type="text" name="name" class="input"
                       placeholder="Например: Иван Петров"
                       value="<?= Html::encode($name) ?>" required autofocus>
            </div>

            <?= Html::submitButton('Создать ссылку-приглашение', ['class' => 'btn-primary w-full']) ?>
        </form>
    </div>

</div>