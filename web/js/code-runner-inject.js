// ============ Резервный список — только если jedi недоступен ============
const PY_FALLBACK_KEYWORDS = ['for','while','if','elif','else','def','return','import','in','not','and','or',
    'True','False','None','break','continue','pass'];

const PY_FALLBACK_FUNCS = ['print','input','int','float','str','len','range','list','dict','set','tuple',
    'sum','min','max','abs','round','sorted','enumerate','map','zip','open','ord','chr','bin','hex','oct'];

/**
 * Клавиатурные привязки в духе VS Code:
 * Home — сначала к первому непробельному символу строки, повторное нажатие — в самое начало.
 * End — в конец строки (без изменений, но явно фиксируем для консистентности).
 */
function buildSmartKeymap() {
    return {
        'Ctrl-Space': 'autocomplete',
        'Home': function (cm) {
            const cur = cm.getCursor();
            const line = cm.getLine(cur.line);
            const firstNonSpace = line.search(/\S/);
            const targetCh = firstNonSpace === -1 ? 0 : firstNonSpace;

            if (cur.ch === targetCh) {
                cm.setCursor({ line: cur.line, ch: 0 });
            } else {
                cm.setCursor({ line: cur.line, ch: targetCh });
            }
        },
        'End': function (cm) {
            const cur = cm.getCursor();
            const line = cm.getLine(cur.line);
            cm.setCursor({ line: cur.line, ch: line.length });
        },
    };
}

function fallbackHint(editor) {
    const cur = editor.getCursor();
    const token = editor.getTokenAt(cur);
    const start = token.start;
    const word = token.string.slice(0, cur.ch - start);

    if (!word || word.length < 2) return null;
    const wordLower = word.toLowerCase();
    const seen = new Set();
    const list = [];

    PY_FALLBACK_FUNCS.forEach(name => {
        if (name.toLowerCase().startsWith(wordLower) && !seen.has(name)) {
            seen.add(name);
            list.push(makeSimpleCallCompletion(name));
        }
    });

    PY_FALLBACK_KEYWORDS.forEach(name => {
        if (name.toLowerCase().startsWith(wordLower) && name.toLowerCase() !== wordLower && !seen.has(name)) {
            seen.add(name);
            list.push(name);
        }
    });

    if (list.length === 0) return null;

    return {
        list,
        from: CodeMirror.Pos(cur.line, start),
        to: CodeMirror.Pos(cur.line, cur.ch),
    };
}

function makeSimpleCallCompletion(name) {
    return {
        text: name,
        displayText: name + '()',
        hint: function (cm, data, completion) {
            cm.replaceRange(completion.text, data.from, data.to);
            const afterNamePos = { line: data.from.line, ch: data.from.ch + completion.text.length };
            cm.replaceRange('()', afterNamePos, afterNamePos);
            cm.setCursor({ line: afterNamePos.line, ch: afterNamePos.ch + 1 });
        },
    };
}

function makeJediCompletion(c, wordStart, cursorPos) {
    const isCallable = c.type === 'function' || c.type === 'class';

    return {
        text: c.name,
        displayText: c.name,
        className: 'py-hint-type-' + c.type,
        hint: function (cm, data, completion) {
            // Заменяем ровно ту часть слова, что уже введена пользователем —
            // от начала текущего слова (wordStart) до текущей позиции курсора (cursorPos),
            // не трогая символ точки и всё что было до него.
            cm.replaceRange(completion.text, wordStart, cursorPos);

            if (isCallable) {
                const afterNamePos = {
                    line: wordStart.line,
                    ch: wordStart.ch + completion.text.length,
                };
                cm.replaceRange('()', afterNamePos, afterNamePos);
                cm.setCursor({ line: afterNamePos.line, ch: afterNamePos.ch + 1 });
            }
        },
    };
}

/**
 * Асинхронное автодополнение через jedi (реальный анализ типов кода).
 * Если jedi недоступен/занят/не успел — использует упрощённый резервный список.
 */
function pythonHint(editor, callback) {
    const cur = editor.getCursor();
    const line = editor.getLine(cur.line);

    // Определяем начало текущего "слова" вручную по символам,
    // не полагаясь на токенизатор CodeMirror — он может некорректно
    // группировать "объект.метод" в один токен.
    let wordStartCh = cur.ch;
    while (wordStartCh > 0 && /[A-Za-z0-9_]/.test(line[wordStartCh - 1])) {
        wordStartCh--;
    }

    const wordStart = CodeMirror.Pos(cur.line, wordStartCh);
    const cursorPos = CodeMirror.Pos(cur.line, cur.ch);

    const code = editor.getValue();
    const jediLine = cur.line + 1; // jedi нумерует строки с 1
    const jediColumn = cur.ch;

    window.PyRunner.getCompletions(code, jediLine, jediColumn).then((completions) => {
        if (!completions || completions.length === 0) {
            callback(fallbackHint(editor));
            return;
        }
        const list = completions.map(c => makeJediCompletion(c, wordStart, cursorPos));
        callback({ list, from: wordStart, to: cursorPos });
    });
}
pythonHint.async = true;


function attachAutoHint(editor) {
    editor.on('inputRead', (cm, change) => {
        if (change.text[0] && /^[\w.]$/.test(change.text[0])) {
            CodeMirror.showHint(cm, pythonHint, { completeSingle: false });
        }
    });
}

// ============ Индикатор готовности Python-окружения ============
function bindPyReadyIndicator(btn, baseTitle, options = {}) {
    const affectOpacity = options.affectOpacity !== false;

    function update() {
        const ready = window.PyRunner.getStatus() === 'ready';
        btn.title = ready
            ? baseTitle
            : 'Python-окружение готовится, запуск начнётся автоматически как только оно будет готово';
        if (affectOpacity) {
            btn.classList.toggle('py-icon-btn-warming', !ready);
        }
    }

    update();
    document.addEventListener('pyodide:status', update);
}

// ============ Кнопки запуска на блоках кода в контенте ============
function attachPythonRunButtons(root) {
    root = root || document;

    const preBlocks = new Set();
    root.querySelectorAll('pre').forEach((pre) => {
        const isPython = pre.classList.contains('language-python') || pre.querySelector('code.language-python');
        const isStatic = pre.classList.contains('no-run');
        if (isPython && !isStatic) {
            preBlocks.add(pre);
        }
    });

    if (preBlocks.size === 0) return;

    window.PyRunner.preload();
    loadCodeMirrorAssets();
    ensurePyModal();

    preBlocks.forEach((pre) => {
        if (pre.dataset.runAttached) return;
        pre.dataset.runAttached = '1';

        const holder = document.createElement('div');
        holder.className = 'py-code-holder';
        pre.parentNode.insertBefore(holder, pre);
        holder.appendChild(pre);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'py-float-run-btn';
        btn.innerHTML =
            '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">' +
            '<path d="M8 5v14l11-7z"/>' +
            '</svg>';

        holder.appendChild(btn);

        bindPyReadyIndicator(btn, 'Запустить и изменить', { affectOpacity: false });

        btn.addEventListener('click', async () => {
            const codeEl = pre.querySelector('code') || pre;
            await loadCodeMirrorAssets();
            openPyModal(codeEl.innerText);
        });
    });
}

let codeMirrorReady = null;

function loadCodeMirrorAssets() {
    if (codeMirrorReady) return codeMirrorReady;

    codeMirrorReady = new Promise((resolve) => {
        if (window.CodeMirror) return resolve();

        ['https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css',
         'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css']
            .forEach(href => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            });

        const scripts = [
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js',
        ];
        let i = 0;
        (function next() {
            if (i >= scripts.length) return resolve();
            const s = document.createElement('script');
            s.src = scripts[i];
            s.onload = () => { i++; next(); };
            document.head.appendChild(s);
        })();
    });

    return codeMirrorReady;
}

// ============ Модальное окно ============
function ensurePyModal() {
    if (document.getElementById('py-modal')) return;

    const modal = document.createElement('div');
    modal.id = 'py-modal';
    modal.className = 'py-modal hidden';
    modal.innerHTML = `
        <div class="py-modal-backdrop"></div>
        <div class="py-modal-panel">
            <div class="py-modal-header">
                <button type="button" class="py-modal-close" title="Закрыть">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
            <div class="py-modal-body">
                <div class="py-modal-editor-col">
                    <textarea id="py-modal-editor"></textarea>
                </div>
                <div class="py-modal-output-col">
                    <div id="py-modal-output" class="py-run-output py-modal-output"></div>
                    <div id="py-modal-output-input-row" class="py-input-row hidden">
                        <input type="text" id="py-modal-input" class="py-input-field" placeholder="Введите значение и нажмите Enter">
                    </div>
                </div>
            </div>
            <div class="py-modal-footer">
                <button type="button" class="py-icon-btn" id="py-modal-restore" title="Вернуть исходный код">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10a7 7 0 1 1 2 5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4v6h6"/>
                    </svg>
                </button>
                <button type="button" class="py-icon-btn py-icon-btn-primary" id="py-modal-run" title="Выполнить">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    modal.querySelector('.py-modal-backdrop').addEventListener('click', closePyModal);
    modal.querySelector('.py-modal-close').addEventListener('click', closePyModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closePyModal();
    });
}

let pyModalEditor = null;
let pyModalOriginalCode = '';

function openPyModal(code) {
    const modal = document.getElementById('py-modal');
    modal.classList.remove('hidden');
    pyModalOriginalCode = code;

    if (!pyModalEditor) {
        pyModalEditor = CodeMirror.fromTextArea(document.getElementById('py-modal-editor'), {
            mode: 'python',
            lineNumbers: true,
            indentUnit: 4,
            tabSize: 4,
            viewportMargin: Infinity,
            lineWrapping: true,
            autoCloseBrackets: true,
            extraKeys: buildSmartKeymap(),
            hintOptions: { hint: pythonHint },
        });
        attachAutoHint(pyModalEditor);

        const runBtn = document.getElementById('py-modal-run');
        bindPyReadyIndicator(runBtn, 'Выполнить');
    }

    pyModalEditor.setValue(code);
    setTimeout(() => pyModalEditor.refresh(), 50);

    const output = document.getElementById('py-modal-output');
    output.textContent = '';

    document.getElementById('py-modal-run').onclick = () =>
        runInteractive(pyModalEditor.getValue(), output, document.getElementById('py-modal-run'));

    document.getElementById('py-modal-restore').onclick = () => {
        pyModalEditor.setValue(pyModalOriginalCode);
    };
}

function closePyModal() {
    document.getElementById('py-modal').classList.add('hidden');
}

async function runInteractive(code, outputEl, runBtn) {
    outputEl.textContent = '';
    runBtn.disabled = true;
    runBtn.classList.add('is-loading');

    const inputRow = document.getElementById(outputEl.id + '-input-row');

    function showInputField() {
        if (!inputRow) return;
        inputRow.classList.remove('hidden');
        const field = inputRow.querySelector('input');
        field.value = '';
        field.focus();

        const onEnter = (e) => {
            if (e.key !== 'Enter') return;
            const value = field.value;
            outputEl.textContent += value + '\n';
            outputEl.scrollTop = outputEl.scrollHeight;
            inputRow.classList.add('hidden');
            field.removeEventListener('keydown', onEnter);
            window.PyRunner.submitInput(value);
        };
        field.addEventListener('keydown', onEnter);
    }

    await window.PyRunner.run(code, {
        onOutput: (text) => {
            outputEl.textContent += text;
            outputEl.scrollTop = outputEl.scrollHeight;
        },
        onInputRequest: showInputField,
    });

    runBtn.disabled = false;
    runBtn.classList.remove('is-loading');
}

document.addEventListener('DOMContentLoaded', () => attachPythonRunButtons());
document.addEventListener('htmx:afterSwap', (e) => attachPythonRunButtons(e.detail.target));