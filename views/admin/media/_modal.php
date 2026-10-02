<?php
use yii\helpers\Url;
?>

<div id="media-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(15,23,42,0.6);"
     onclick="if(event.target === this) closeMediaModal()">

    <div class="bg-white rounded-2xl w-full max-w-2xl flex flex-col"
         style="max-height: 80vh; box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 10px 32px rgba(0,0,0,0.10);">

        <div class="flex items-center justify-between p-6 pb-4 shrink-0" style="border-bottom: 1px solid #E2E8F0;">
            <h3 class="text-lg font-semibold text-base-100">Выбрать изображение или файл</h3>
            <button type="button" onclick="closeMediaModal()" class="btn-ghost">✕</button>
        </div>

        <div id="media-picker-content" class="overflow-y-auto p-6" style="flex: 1;">
            <!-- Загружается через HTMX при открытии -->
        </div>

    </div>
</div>

<script>
let activeTextareaId = null;

function openMediaModal(textareaId) {
    activeTextareaId = textareaId;
    const modal = document.getElementById('media-modal');
    modal.classList.remove('hidden');

    htmx.ajax('GET', '<?= Url::to(['/admin/media/picker']) ?>', {
        target: '#media-picker-content',
        swap: 'innerHTML',
    });
}

function closeMediaModal() {
    document.getElementById('media-modal').classList.add('hidden');
    activeTextareaId = null;
}

function selectMediaFile(url, filename, isImage) {
    if (!activeTextareaId) return;

    const textarea = document.getElementById(activeTextareaId);
    const tag = isImage ? `![${filename}](${url})` : `[${filename}](${url})`;

    const start = textarea.selectionStart;
    const end   = textarea.selectionEnd;
    const text  = textarea.value;

    textarea.value = text.substring(0, start) + '\n' + tag + '\n' + text.substring(end);
    textarea.dispatchEvent(new Event('input'));

    closeMediaModal();
}
</script>