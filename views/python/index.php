<?php
/** @var yii\web\View $this */
use yii\helpers\Html;

$this->title = 'Питон-песочница';

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js');
?>

<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="mb-4">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Питон-песочница</h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-3">
        <div class="card flex-1 min-w-0">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-base-100">Код</p>
                <div class="flex items-center gap-2">
                    <button type="button" id="sandbox-reset" class="py-icon-btn" title="Очистить код">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                        </svg>
                    </button>
                    <button type="button" id="sandbox-run" class="py-icon-btn py-icon-btn-primary" title="Выполнить">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
            </div>

            <textarea id="sandbox-editor"></textarea>
        </div>

        <div class="card flex-1 min-w-0">
            <p class="text-sm font-semibold text-base-100 mb-3">Вывод</p>
            <div id="sandbox-output" class="py-run-output py-run-output-tall"></div>
            <div id="sandbox-output-input-row" class="py-input-row hidden">
                <input type="text" id="sandbox-input-field" class="py-input-field" placeholder="Введите значение и нажмите Enter">
            </div>
        </div>

    </div>
</div>

<style>
    .CodeMirror {
        height: auto;
        min-height: 340px;
        border-radius: 0.6rem;
        border: 1px solid #E2E8F0;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
    }
    .CodeMirror-scroll {
        overflow-x: hidden !important;
    }
    .CodeMirror-lines {
        overflow-wrap: anywhere;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const editor = CodeMirror.fromTextArea(document.getElementById('sandbox-editor'), {
        mode: 'python',
        lineNumbers: true,
        indentUnit: 4,
        tabSize: 4,
        theme: 'default',
        viewportMargin: Infinity,
        lineWrapping: true,
        autoCloseBrackets: true,
        extraKeys: buildSmartKeymap(),
        hintOptions: { hint: pythonHint },
    });
    attachAutoHint(editor);

    if (editor.lineCount() < 15) {
        const padding = Array(15 - editor.lineCount()).fill('').join('\n');
        editor.setValue(editor.getValue() + padding);
        editor.setCursor(0, 0);
    }

    const runBtn   = document.getElementById('sandbox-run');
    const resetBtn = document.getElementById('sandbox-reset');
    const output   = document.getElementById('sandbox-output');

    window.PyRunner.preload();
    bindPyReadyIndicator(runBtn, 'Выполнить');

    runBtn.addEventListener('click', () => runInteractive(editor.getValue(), output, runBtn));

    resetBtn.addEventListener('click', () => {
        editor.setValue('\n\n\n\n\n\n\n\n\n\n');
        editor.setCursor(0, 0);
        output.textContent = '';
        editor.focus();
    });

    if (!window.PyRunner.supportsSharedArrayBuffer()) {
        output.textContent = 'Примечание: интерактивный ввод через input() может быть недоступен в этом браузере/окружении.\n';
    }
});
</script>