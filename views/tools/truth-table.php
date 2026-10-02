<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\assets\KatexAsset;

KatexAsset::register($this);

$this->title = 'Конструктор таблиц истинности — онлайн';
$this->registerMetaTag([
    'name'    => 'description',
    'content' => 'Онлайн-конструктор таблиц истинности для логических выражений. Постройте эталонную таблицу для проверки задания №2 ЕГЭ по информатике.',
]);
$this->registerJsFile('@web/js/truth-table.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/tools" class="hover:text-acid-lime no-underline">Инструменты</a>
        <span>/</span>
        <span class="text-base-100">Конструктор таблиц истинности</span>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Конструктор таблиц истинности</h1>
        <p class="text-base-400">
            Введите логическое выражение — получите эталонную таблицу со всеми промежуточными шагами для сверки (задание №2).
        </p>
    </div>

    <div class="tool-layout">

        <div class="tool-main">
            <div class="card mb-6">
                <label>Логическое выражение</label>
                <input type="text" id="tt-input" class="input font-mono mb-3"
                       placeholder="Например: (A ∨ B) ∧ ¬C" autofocus>

                <div class="flex flex-wrap gap-1.5 mb-4">
                    <button type="button" class="tt-op-btn" data-insert="¬">¬ НЕ</button>
                    <button type="button" class="tt-op-btn" data-insert="∧">∧ И</button>
                    <button type="button" class="tt-op-btn" data-insert="∨">∨ ИЛИ</button>
                    <button type="button" class="tt-op-btn" data-insert="⊕">⊕ XOR</button>
                    <button type="button" class="tt-op-btn" data-insert="→">→ Импликация</button>
                    <button type="button" class="tt-op-btn" data-insert="≡">≡ Эквивалентность</button>
                    <button type="button" class="tt-op-btn" data-insert="(">(</button>
                    <button type="button" class="tt-op-btn" data-insert=")">)</button>
                </div>

                <button type="button" id="tt-build" class="btn-primary w-full py-2.5">Построить таблицу</button>
            </div>

            <div id="tt-error" class="alert-error mb-6 hidden"></div>

            <div id="tt-result-card" class="card hidden overflow-x-auto">
                <div id="tt-table-wrap"></div>
            </div>
        </div>

        <aside class="tool-sidebar">
            <div class="card lg:sticky" style="top: 84px;">
                <h2 class="text-base font-bold text-base-100 mb-3">Логические операции</h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-semibold text-base-100">$\overline{A}$ — отрицание (НЕ)</p>
                        <p class="text-base-400 text-xs">Истинно, когда A ложно.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">$A \wedge B$ — конъюнкция (И)</p>
                        <p class="text-base-400 text-xs">Истинно, только если оба A и B истинны.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">$A \vee B$ — дизъюнкция (ИЛИ)</p>
                        <p class="text-base-400 text-xs">Истинно, если хотя бы одно из A, B истинно.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">$A \oplus B$ — исключающее ИЛИ</p>
                        <p class="text-base-400 text-xs">Истинно, если A и B различны.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">$A \rightarrow B$ — импликация</p>
                        <p class="text-base-400 text-xs">Ложно только когда A истинно, а B ложно.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-base-100">$A \leftrightarrow B$ — эквивалентность</p>
                        <p class="text-base-400 text-xs">Истинно, когда A и B совпадают по значению.</p>
                    </div>
                </div>

                <p class="text-xs text-base-400 mt-4">
                    Приоритет операций (от высшего к низшему): отрицание, И, исключающее ИЛИ / ИЛИ, импликация, эквивалентность.
                    Используйте скобки, чтобы задать нужный порядок явно.
                </p>
            </div>
        </aside>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input      = document.getElementById('tt-input');
    const buildBtn   = document.getElementById('tt-build');
    const errorBox   = document.getElementById('tt-error');
    const resultCard = document.getElementById('tt-result-card');
    const tableWrap  = document.getElementById('tt-table-wrap');

    document.querySelectorAll('.tt-op-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const insert = btn.dataset.insert;
            const start = input.selectionStart ?? input.value.length;
            const end   = input.selectionEnd ?? input.value.length;
            input.value = input.value.slice(0, start) + insert + input.value.slice(end);
            input.focus();
            input.selectionStart = input.selectionEnd = start + insert.length;
        });
    });

    buildBtn.addEventListener('click', () => {
        errorBox.classList.add('hidden');
        resultCard.classList.add('hidden');

        let data;
        try {
            data = window.TruthTableTool.build(input.value);
        } catch (e) {
            errorBox.textContent = e.message || String(e);
            errorBox.classList.remove('hidden');
            return;
        }

        const { columns, rows } = data;

        let html = '<table class="tt-table"><thead><tr>';
        columns.forEach(col => {
            html += `<th class="${col.isResult ? 'tt-th-result' : ''}">$${col.label}$</th>`;
        });
        html += '</tr></thead><tbody>';

        rows.forEach(row => {
            html += '<tr>';
            row.forEach((val, i) => {
                html += `<td class="${columns[i].isResult ? 'tt-td-result' : ''}">${val}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';

        tableWrap.innerHTML = html;
        window.renderMathIn(tableWrap);
        resultCard.classList.remove('hidden');
    });

    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') buildBtn.click(); });
});
</script>