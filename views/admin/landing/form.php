<?php
/** @var app\models\Landing|null $landing */
/** @var string|null $error */
/** @var bool $isNew */
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $isNew ? 'Новый лендинг' : $landing->title;
?>

<div class="flex items-center gap-2 text-sm text-base-400 mb-6">
    <a href="<?= Url::to(['/admin/landing/index']) ?>" class="hover:text-base-100 no-underline">Лендинги</a>
    <span>/</span>
    <span class="text-base-100"><?= $isNew ? 'Новый' : Html::encode($landing->title) ?></span>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-error mb-4"><?= Html::encode($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="grid gap-6" style="grid-template-columns: 1fr 320px;">

        <!-- Контент -->
        <div class="card">
            <h2 class="text-base font-semibold text-base-100 mb-4">HTML-контент лендинга</h2>
            <div class="field-group">
                <textarea name="content" rows="24" class="input font-mono text-xs"
                          placeholder="Вставьте готовый HTML лендинга..."><?= $isNew ? '' : Html::encode($landing->content) ?></textarea>
            </div>
            <p class="text-xs text-base-400">
                Стилизуйте страницу самостоятельно (инлайн-стили или &lt;style&gt; внутри HTML).
                Форма заявки добавится автоматически под контентом.
            </p>
        </div>

        <!-- Настройки -->
        <div class="space-y-4">

            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-4">Основное</h2>

                <div class="field-group">
                    <label>Название (для админки) *</label>
                    <input type="text" name="title" class="input" required
                           value="<?= $isNew ? '' : Html::encode($landing->title) ?>">
                </div>

                <?php if ($isNew): ?>
                <div class="field-group">
                    <label>URL (необязательно — иначе из названия)</label>
                    <input type="text" name="slug" class="input" placeholder="ege-2026">
                </div>
                <?php else: ?>
                <div class="field-group">
                    <label>URL</label>
                    <input type="text" class="input" value="/<?= $landing->slug ?>" disabled>
                </div>
                <?php endif; ?>

                <div class="field-group">
                    <label>Заголовок формы заявки</label>
                    <input type="text" name="form_title" class="input"
                           value="<?= $isNew ? 'Оставить заявку' : Html::encode($landing->form_title) ?>">
                </div>

                <div class="field-group mb-0">
                    <label>Статус</label>
                    <select name="status" class="input">
                        <option value="draft" <?= (!$isNew && $landing->status === 'draft') ? 'selected' : '' ?>>Черновик</option>
                        <option value="published" <?= (!$isNew && $landing->status === 'published') ? 'selected' : '' ?>>Опубликован</option>
                    </select>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <h2 class="text-sm font-semibold text-base-100 mb-4">SEO</h2>

                <div class="field-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" class="input"
                           placeholder="Если пусто — берётся название"
                           value="<?= $isNew ? '' : Html::encode($landing->meta_title) ?>">
                </div>

                <div class="field-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" class="input" rows="3"
                              placeholder="До 160 символов"><?= $isNew ? '' : Html::encode($landing->meta_description) ?></textarea>
                </div>

                <div class="field-group">
                    <label>Изображение для соцсетей (og:image)</label>
                    <?php if (!$isNew && $landing->og_image): ?>
                        <img src="/files/<?= $landing->og_image ?>" class="w-full rounded-lg mb-2" style="max-height: 100px; object-fit: cover;">
                    <?php endif; ?>
                    <input type="file" name="og_image_file" accept="image/*"
                           class="text-xs text-base-400 file:btn-secondary file:mr-2 file:text-xs file:py-1">
                </div>

                <div class="field-group mb-0">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_indexed" value="1"
                               class="accent-acid-lime"
                               <?= ($isNew || $landing->is_indexed) ? 'checked' : '' ?>>
                        <span>Разрешить индексацию поисковиками</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2">
                <?= Html::submitButton($isNew ? 'Создать лендинг' : 'Сохранить', ['class' => 'btn-primary flex-1']) ?>
                <button type="button" onclick="openPreview()" class="btn-secondary px-3">Предпросмотр</button>
                <?php if (!$isNew): ?>
                    <a href="/<?= $landing->slug ?>" target="_blank" class="btn-secondary px-3">Открыть</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <!-- Модалка предпросмотра -->
<div id="preview-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(15,23,42,0.7);"
     onclick="if(event.target === this) closePreview()">
    <div class="bg-white rounded-2xl w-full flex flex-col" style="max-width: 900px; height: 85vh;">
        <div class="flex items-center justify-between p-4" style="border-bottom: 1px solid #E2E8F0;">
            <h3 class="font-semibold text-base-100">Предпросмотр лендинга</h3>
            <button type="button" onclick="closePreview()" class="btn-ghost">✕</button>
        </div>
        <iframe id="preview-frame" style="flex:1; border:0; width:100%;"></iframe>
    </div>
</div>

<script>
function openPreview() {
    const content   = document.querySelector('[name="content"]').value;
    const formTitle = document.querySelector('[name="form_title"]').value;
    const token     = document.querySelector('meta[name="csrf-token"]').content;

    fetch('<?= Url::to(['/admin/landing/preview']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': token,
        },
        body: 'content=' + encodeURIComponent(content) + '&form_title=' + encodeURIComponent(formTitle),
    })
    .then(r => r.text())
    .then(html => {
        const frame = document.getElementById('preview-frame');
        frame.srcdoc = html;
        document.getElementById('preview-modal').classList.remove('hidden');
    });
}

function closePreview() {
    document.getElementById('preview-modal').classList.add('hidden');
}
</script>
</form>