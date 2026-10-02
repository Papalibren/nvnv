<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\assets\KatexAsset;
KatexAsset::register($this);

$this->title = 'Перевод систем счисления — онлайн калькулятор';
$this->registerMetaTag([
    'name'    => 'description',
    'content' => 'Онлайн-калькулятор перевода чисел между системами счисления: двоичной, восьмеричной, десятичной, шестнадцатеричной. Решение по шагам для подготовки к ЕГЭ по информатике.',
]);
$this->registerJsFile('@web/js/number-systems.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="max-w-6xl mx-auto px-4 py-10">

    <div class="flex items-center gap-2 text-sm text-base-400 mb-6">
        <a href="/tools" class="hover:text-acid-lime no-underline">Инструменты</a>
        <span>/</span>
        <span class="text-base-100">Перевод систем счисления</span>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-base-100 mb-2">Перевод систем счисления</h1>
        <p class="text-base-400">Введите число — калькулятор покажет полное решение по шагам, как в тетради.</p>
    </div>

    <div class="tool-layout">

        <!-- Калькулятор -->
        <div class="tool-main">
            <div class="card mb-6">
                <div class="grid grid-cols-3 gap-4 items-end mb-4">
                    <div>
                        <label>Число</label>
                        <input type="text" id="ns-input" class="input font-mono" placeholder="Например: 101101" autofocus>
                    </div>
                    <div>
                        <label>Из системы</label>
                        <select id="ns-from" class="input"></select>
                    </div>
                    <div>
                        <label>В систему</label>
                        <select id="ns-to" class="input"></select>
                    </div>
                </div>

                <button type="button" id="ns-convert" class="btn-primary w-full py-2.5">Перевести</button>
            </div>

            <div id="ns-error" class="alert-error mb-6 hidden"></div>

            <div id="ns-result-card" class="card hidden">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-base-400">Результат</p>
                    <p id="ns-result-value" class="text-2xl font-bold text-acid-lime font-mono"></p>
                </div>
                <div id="ns-steps"></div>
            </div>
        </div>

        <!-- Теория -->
        <aside class="tool-sidebar">
            <div class="card lg:sticky" style="top: 84px;">
                <h2 class="text-base font-bold text-base-100 mb-3">Что такое система счисления</h2>
                <p class="text-sm text-base-400 mb-4">
                    Система счисления — это способ записи чисел с помощью набора цифр.
                    Число цифр в наборе называется <strong>основанием</strong>.
                </p>

                <table class="w-full text-xs mb-4" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background:#F1F5F9;">
                            <th class="text-left px-2 py-2" style="border-bottom:2px solid #E2E8F0;">Система</th>
                            <th class="text-left px-2 py-2" style="border-bottom:2px solid #E2E8F0;">Цифры</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="px-2 py-1.5" style="border-bottom:1px solid #EDF1F7;">Двоичная (2)</td><td class="px-2 py-1.5 font-mono" style="border-bottom:1px solid #EDF1F7;">0, 1</td></tr>
                        <tr><td class="px-2 py-1.5" style="border-bottom:1px solid #EDF1F7;">Восьмеричная (8)</td><td class="px-2 py-1.5 font-mono" style="border-bottom:1px solid #EDF1F7;">0–7</td></tr>
                        <tr><td class="px-2 py-1.5" style="border-bottom:1px solid #EDF1F7;">Десятичная (10)</td><td class="px-2 py-1.5 font-mono" style="border-bottom:1px solid #EDF1F7;">0–9</td></tr>
                        <tr><td class="px-2 py-1.5">Шестнадцатеричная (16)</td><td class="px-2 py-1.5 font-mono">0–9, A–F</td></tr>
                    </tbody>
                </table>

                <p class="text-xs text-base-400 mb-2">
                    <strong>Перевод в десятичную:</strong> каждая цифра умножается на основание в степени её позиции, суммы складываются.
                </p>
                <p class="text-xs text-base-400">
                    <strong>Перевод из десятичной:</strong> число делится в столбик на новое основание, остатки читаются снизу вверх.
                </p>
            </div>
        </aside>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input          = document.getElementById('ns-input');
    const fromSelect      = document.getElementById('ns-from');
    const toSelect        = document.getElementById('ns-to');
    const btn             = document.getElementById('ns-convert');
    const errorBox        = document.getElementById('ns-error');
    const resultCard      = document.getElementById('ns-result-card');
    const resultValue     = document.getElementById('ns-result-value');
    const stepsBox        = document.getElementById('ns-steps');

    const FIXED_BASES = [
        { value: '2', label: 'Двоичная (2)' },
        { value: '8', label: 'Восьмеричная (8)' },
        { value: '10', label: 'Десятичная (10)' },
        { value: '16', label: 'Шестнадцатеричная (16)' },
    ];

    function populateSelect(select, excludeValue, preferredValue) {
        const available = FIXED_BASES.filter(b => b.value !== excludeValue);
        const currentValue = preferredValue && available.some(b => b.value === preferredValue)
            ? preferredValue
            : available[0].value;

        select.innerHTML = available.map(b =>
            `<option value="${b.value}" ${b.value === currentValue ? 'selected' : ''}>${b.label}</option>`
        ).join('') + `<option value="custom">Другое основание…</option>`;

        select.dataset.customBase = '2';
    }

    function getBase(select) {
        if (select.value === 'custom') {
            return parseInt(select.dataset.customBase || '2', 10);
        }
        return parseInt(select.value, 10);
    }

    function refresh(changed) {
        if (changed === 'from') {
            populateSelect(toSelect, fromSelect.value === 'custom' ? null : fromSelect.value, toSelect.value);
        } else {
            populateSelect(fromSelect, toSelect.value === 'custom' ? null : toSelect.value, fromSelect.value);
        }
        toggleCustomPrompt();
    }

    function toggleCustomPrompt() {
        [fromSelect, toSelect].forEach((select) => {
            if (select.value === 'custom' && !select.dataset.promptedOnce) {
                const val = prompt('Введите основание системы счисления (от 2 до 36):', select.dataset.customBase || '2');
                const num = parseInt(val, 10);
                if (num >= 2 && num <= 36) {
                    select.dataset.customBase = String(num);
                }
                select.dataset.promptedOnce = '1';
            }
        });
    }

    populateSelect(fromSelect, null, '2');
    populateSelect(toSelect, fromSelect.value, '10');

    fromSelect.addEventListener('change', () => { fromSelect.dataset.promptedOnce = ''; refresh('from'); });
    toSelect.addEventListener('change', () => { toSelect.dataset.promptedOnce = ''; refresh('to'); });

    btn.addEventListener('click', () => {
        const fromBase = getBase(fromSelect);
        const toBase   = getBase(toSelect);

        errorBox.classList.add('hidden');
        resultCard.classList.add('hidden');

        if (fromBase === toBase) {
            errorBox.textContent = 'Основания совпадают — выберите разные системы счисления.';
            errorBox.classList.remove('hidden');
            return;
        }

        const { html, result, error } = window.NumberSystemsTool.convert(input.value, fromBase, toBase);

        if (error) {
            errorBox.textContent = error;
            errorBox.classList.remove('hidden');
            return;
        }

        resultValue.textContent = result;
        stepsBox.innerHTML = html;
        window.renderMathIn(stepsBox);
        resultCard.classList.remove('hidden');
    });

    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') btn.click(); });
});
</script>