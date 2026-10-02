<?php
/** @var yii\web\View $this */
/** @var app\models\MediaFile[] $files */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!-- Форма загрузки — всегда видна сверху, не зависит от галереи -->
<div class="p-3 rounded-lg bg-base-900 mb-4 shrink-0">
    <form hx-post="<?= Url::to(['/admin/media/upload']) ?>"
          hx-target="#media-picker-content"
          hx-swap="innerHTML"
          hx-encoding="multipart/form-data"
          class="flex items-center gap-3">
        <label class="btn-secondary text-xs py-1.5 px-3 cursor-pointer shrink-0">
            Выбрать файл
            <input type="file" name="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                   class="hidden" onchange="this.form.requestSubmit()">
        </label>
        <span class="text-xs text-base-400">
            Изображения, PDF, Word, Excel — до 10MB
        </span>
    </form>
</div>

<!-- Галерея -->
<?php if (empty($files)): ?>
    <p class="text-base-400 text-sm text-center py-8">Файлов пока нет. Загрузите первый файл выше.</p>
<?php else: ?>
    <div class="grid gap-3" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <?php foreach ($files as $file): ?>
            <div class="relative group cursor-pointer rounded-lg overflow-hidden border border-base-700 hover:border-acid-lime transition-colors duration-150"
                 style="aspect-ratio: 1; display: flex; flex-direction: column;"
                 onclick="selectMediaFile('<?= $file->getUrl() ?>', '<?= Html::encode(addslashes($file->filename)) ?>', <?= $file->isImage() ? 'true' : 'false' ?>)">

                <?php if ($file->isImage()): ?>
                    <img src="<?= $file->getUrl() ?>" alt=""
                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-base-900">
                        <svg class="w-8 h-8 text-base-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                <?php endif; ?>

                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-150 flex items-center justify-center">
                    <span class="opacity-0 group-hover:opacity-100 text-white text-xs font-medium transition-opacity duration-150">
                        Вставить
                    </span>
                </div>

                <p class="absolute bottom-0 left-0 right-0 text-xs text-white truncate px-1.5 py-1"
                   style="background: rgba(0,0,0,0.6);">
                    <?= Html::encode($file->filename) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>