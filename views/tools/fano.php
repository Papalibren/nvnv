<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\assets\KatexAsset;

KatexAsset::register($this);

$this->title = 'Условие Фано — проверка и декодирование';
$this->registerMetaTag([
    'name'    => 'description',
    'content' => 'Онлайн-проверка условия Фано для неравномерного кода и демонстрация однозначного декодирования. Задание №4 ЕГЭ по информатике.',
]);
$this->registerJsFile('@web/js/fano-tool.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/tools" class="hover:text-acid-lime no-underline">Инструменты</a>
        <span>/</span>
        <span class="text-base-100">Условие Фано</span>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Условие Фано</h1>
        <p class="text-base-400">
            Проверьте, удовлетворяет ли неравномерный код условию Фано, и посмотрите однозначное декодирование по шагам.
        </p>
    </div>

    <div class="tool-layout">

        <div class="tool-main">

            <!-- Ввод кодов -->
            <div class="card mb-6">
                <p class="text-sm font-semibold text-base-100 mb-3">Символы и их коды</p>

                <div id="fano-rows" class="space-y-2 mb-3"></div>

                <button type="button" id="fano-add-row" class="btn-secondary text-xs py-1.5 px-3">+ Добавить символ</button>
            </div>

            <button type="button" id="fano-check" class="btn-primary w-full py-2.5 mb-6">Проверить условие Фано</button>

            <div id="fano-error" class="alert-error mb-6 hidden"></div>

            <!-- Результат: вердикт + дерево -->
            <div id="fano-verdict-card" class="card mb-6 hidden">
                <div id="fano-verdict"></div>
            </div>

            <div id="fano-tree-card" class="card mb-6 hidden">
                <p class="text-sm font-semibold text-base-100 mb-3">Дерево кодов</p>
                <p class="text-xs text-base-400 mb-4">
                    Зелёные узлы — символы на листьях (условие выполнено).
                    Красные — символ оказался на внутреннем узле (нарушение: этот код является началом другого).
                </p>
                <div id="fano-tree-svg" class="overflow-x-auto"></div>
            </div>

            <!-- Декодирование -->
            <div id="fano-decode-card" class="card hidden">
                <p class="text-sm font-semibold text-base-100 mb-3">Проверить декодирование</p>
                <div class="flex gap-2 mb-4">
                    <input type="text" id="fano-decode-input" class="input font-mono" placeholder="Например: 0110111">
                    <button type="button" id="fano-decode-btn" class="btn-primary px-6 shrink-0">Декодировать</button>
                </div>
                <div id="fano-decode-result"></div>
            </div>

        </div>

        <aside class="tool-sidebar">
            <div class="card lg:sticky" style="top: 84px;">
                <h2 class="text-base font-bold text-base-100 mb-3">Что такое условие Фано</h2>
                <p class="text-sm text-base-400 mb-4">
                    Неравномерный код удовлетворяет <strong>условию Фано</strong>, если ни один код символа
                    не является началом (префиксом) кода другого символа.
                </p>
                <p class="text-sm text-base-400 mb-4">
                    Это гарантирует, что закодированное сообщение можно разбить на символы
                    <strong>единственным способом</strong> — без разделителей между кодами.
                </p>

                <div class="ns-theory-row mb-2">Верно: А=0, Б=10, В=110, Г=111</div>
                <div class="ns-theory-row" style="background:#FEE2E2;">Неверно: А=0, Б=01 — код Б начинается с кода А</div>

                <p class="text-xs text-base-400 mt-4">
                    В задании №4 ЕГЭ обычно нужно определить длины кодов при условии Фано
                    так, чтобы сообщение было закодировано минимальным числом бит.
                </p>
            </div>
        </aside>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const rowsBox     = document.getElementById('fano-rows');
    const addRowBtn   = document.getElementById('fano-add-row');
    const checkBtn    = document.getElementById('fano-check');
    const errorBox    = document.getElementById('fano-error');
    const verdictCard = document.getElementById('fano-verdict-card');
    const verdictBox  = document.getElementById('fano-verdict');
    const treeCard    = document.getElementById('fano-tree-card');
    const treeSvgBox  = document.getElementById('fano-tree-svg');
    const decodeCard  = document.getElementById('fano-decode-card');

    let currentPairs = [];

    function addRow(symbol = '', code = '') {
        const row = document.createElement('div');
        row.className = 'flex gap-2 items-center fano-row';
        row.innerHTML = `
            <input type="text" class="input font-mono fano-symbol" placeholder="A" maxlength="3" style="width:80px;" value="${symbol}">
            <input type="text" class="input font-mono fano-code" placeholder="код, напр. 10" value="${code}">
            <button type="button" class="btn-ghost text-xs text-acid-pink fano-remove-row">✕</button>
        `;
        rowsBox.appendChild(row);
        row.querySelector('.fano-remove-row').addEventListener('click', () => row.remove());
    }

    // Стартовые примерные строки
    addRow('А', '0');
    addRow('Б', '10');
    addRow('В', '110');
    addRow('Г', '111');

    addRowBtn.addEventListener('click', () => addRow());

    function readPairs() {
        return Array.from(rowsBox.querySelectorAll('.fano-row')).map(row => ({
            symbol: row.querySelector('.fano-symbol').value.trim(),
            code: row.querySelector('.fano-code').value.trim(),
        }));
    }

    checkBtn.addEventListener('click', () => {
        errorBox.classList.add('hidden');
        verdictCard.classList.add('hidden');
        treeCard.classList.add('hidden');
        decodeCard.classList.add('hidden');

        const pairs = readPairs();
        const err = window.FanoTool.validateCodes(pairs);
        if (err) {
            errorBox.textContent = err;
            errorBox.classList.remove('hidden');
            return;
        }

        currentPairs = pairs;
        const violations = window.FanoTool.checkFano(pairs);

        if (violations.length === 0) {
            verdictBox.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="font-semibold text-base-100">Условие Фано выполнено</p>
                        <p class="text-sm text-base-400">Сообщение декодируется однозначно.</p>
                    </div>
                </div>`;
            decodeCard.classList.remove('hidden');
        } else {
            const list = violations.map(v =>
                `код «${v.prefix.code}» (${v.prefix.symbol}) является началом кода «${v.full.code}» (${v.full.symbol})`
            ).join('; ');
            verdictBox.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="text-2xl">❌</span>
                    <div>
                        <p class="font-semibold text-base-100">Условие Фано нарушено</p>
                        <p class="text-sm text-base-400">${list}.</p>
                    </div>
                </div>`;
        }

        verdictCard.classList.remove('hidden');

        treeSvgBox.innerHTML = window.FanoTool.renderTreeSvg(pairs);
        treeCard.classList.remove('hidden');
    });

    document.getElementById('fano-decode-btn').addEventListener('click', () => {
        const input = document.getElementById('fano-decode-input').value;
        const resultBox = document.getElementById('fano-decode-result');

        const { error, steps, decoded } = window.FanoTool.decode(input, currentPairs);

        if (error) {
            resultBox.innerHTML = `<div class="alert-error">${error}</div>`;
            return;
        }

        let html = '<div class="fano-decode-steps">';
        steps.forEach((s, i) => {
            html += `
                <div class="fano-decode-step">
                    <span class="fano-decode-code">${s.code}</span>
                    <span class="fano-decode-arrow">→</span>
                    <span class="fano-decode-symbol">${s.symbol}</span>
                </div>`;
        });
        html += '</div>';
        html += `<p class="text-sm text-base-400 mt-3">Результат: <strong class="text-base-100">${decoded}</strong></p>`;

        resultBox.innerHTML = html;
    });
});
</script>