<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\assets\KatexAsset;
KatexAsset::register($this);

$this->title = 'Перевод бит, байт, КБ, МБ, ГБ, ТБ — онлайн калькулятор';
$this->registerMetaTag([
    'name'    => 'description',
    'content' => 'Онлайн-калькулятор перевода единиц измерения информации: биты, байты, килобайты, мегабайты, гигабайты, терабайты. Решение по шагам для подготовки к ЕГЭ по информатике.',
]);
$this->registerJsFile('@web/js/bits-bytes.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/tools" class="hover:text-acid-lime no-underline">Инструменты</a>
        <span>/</span>
        <span class="text-base-100">Перевод бит, байт, КБ, МБ</span>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Перевод единиц измерения информации</h1>
        <p class="text-base-400">Биты, байты, килобайты, мегабайты, гигабайты и терабайты — с решением по шагам.</p>
    </div>

    <div class="tool-layout">

        <!-- Калькулятор -->
        <div class="tool-main">
            <div class="card mb-6">
                <div class="grid grid-cols-3 gap-4 items-end mb-4">
                    <div>
                        <label>Значение</label>
                        <input type="text" id="bb-input" class="input font-mono" placeholder="Например: 5" autofocus>
                    </div>
                    <div>
                        <label>Из единицы</label>
                        <select id="bb-from" class="input"></select>
                    </div>
                    <div>
                        <label>В единицу</label>
                        <select id="bb-to" class="input"></select>
                    </div>
                </div>

                <button type="button" id="bb-convert" class="btn-primary w-full py-2.5">Перевести</button>
            </div>

            <div id="bb-error" class="alert-error mb-6 hidden"></div>

            <div id="bb-result-card" class="card hidden">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-base-400">Результат</p>
                    <p id="bb-result-value" class="text-2xl font-bold text-acid-lime font-mono"></p>
                </div>
                <div id="bb-steps"></div>
            </div>
        </div>

        <!-- Теория — справа на широких экранах, снизу на мобильных -->
<aside class="tool-sidebar">
    <div class="card lg:sticky" style="top: 84px;">
        <h2 class="text-base font-bold text-base-100 mb-3">Как соотносятся единицы</h2>
        <p class="text-sm text-base-400 mb-4">
            В информатике используется <strong>двоичная система приставок</strong>:
            каждая следующая единица больше предыдущей в 1024 раза, кроме бита и байта — там коэффициент 8.
        </p>

        <div class="ns-theory-list">
            <div class="ns-theory-row">$1024 = 2^{10}$</div>
            <div class="ns-theory-row">$1 \text{ Б} = 2^{3} \text{ бит} = 8 \text{ бит}$</div>
            <div class="ns-theory-row">$1 \text{ КБ} = 2^{10} \text{ Б} = 2^{13} \text{ бит}$</div>
            <div class="ns-theory-row">$1 \text{ МБ} = 2^{10} \text{ КБ} = 2^{20} \text{ Б} = 2^{23} \text{ бит}$</div>
            <div class="ns-theory-row">$1 \text{ ГБ} = 2^{10} \text{ МБ} = 2^{30} \text{ Б} = 2^{33} \text{ бит}$</div>
            <div class="ns-theory-row">$1 \text{ ТБ} = 2^{10} \text{ ГБ} = 2^{40} \text{ Б} = 2^{43} \text{ бит}$</div>
        </div>

        <p class="text-xs text-base-400 mt-4">
            Переводить лучше по цепочке через соседние единицы, а не сразу через большой коэффициент —
            так видно, откуда берётся каждое число.
        </p>
    </div>
</aside>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input      = document.getElementById('bb-input');
    const fromSelect  = document.getElementById('bb-from');
    const toSelect    = document.getElementById('bb-to');
    const btn         = document.getElementById('bb-convert');
    const errorBox    = document.getElementById('bb-error');
    const resultCard  = document.getElementById('bb-result-card');
    const resultValue = document.getElementById('bb-result-value');
    const stepsBox    = document.getElementById('bb-steps');

    const UNITS = window.BitsBytesTool.UNITS;

    // Заполняем оба select, исключая текущее значение другого
    function populateSelect(select, excludeKey, preferredKey) {
        const available = UNITS.filter(u => u.key !== excludeKey);
        const currentValue = preferredKey && available.some(u => u.key === preferredKey)
            ? preferredKey
            : available[0].key;

        select.innerHTML = available.map(u =>
            `<option value="${u.key}" ${u.key === currentValue ? 'selected' : ''}>${u.label}</option>`
        ).join('');
    }

    function refreshSelects(changed) {
        if (changed === 'from') {
            populateSelect(toSelect, fromSelect.value, toSelect.value);
        } else {
            populateSelect(fromSelect, toSelect.value, fromSelect.value);
        }
    }

    // Начальное состояние: байт → КБ
    populateSelect(fromSelect, null, 'byte');
    populateSelect(toSelect, fromSelect.value, 'kb');

    fromSelect.addEventListener('change', () => refreshSelects('from'));
    toSelect.addEventListener('change', () => refreshSelects('to'));

    btn.addEventListener('click', () => {
        errorBox.classList.add('hidden');
        resultCard.classList.add('hidden');

        const { error, result, steps } = window.BitsBytesTool.convert(input.value, fromSelect.value, toSelect.value);

        if (error) {
            errorBox.textContent = error;
            errorBox.classList.remove('hidden');
            return;
        }

        const toLabel = UNITS.find(u => u.key === toSelect.value).label;
        resultValue.textContent = result + ' ' + toLabel;

        stepsBox.innerHTML = steps.map(s => `
            <div class="ns-step">
                <p class="ns-step-title">${s.title}</p>
                <div class="ns-expansion-values">${s.formula}</div>
            </div>
        `).join('');

        resultCard.classList.remove('hidden');
    });

    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') btn.click(); });
});
</script>