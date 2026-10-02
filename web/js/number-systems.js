(function () {
    const DIGITS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    function digitValue(ch) { return DIGITS.indexOf(ch.toUpperCase()); }
    function digitChar(n) { return DIGITS[Number(n)]; }

    function validate(str, base) {
        if (!str || str.trim() === '') return 'Введите число.';
        const clean = str.trim().toUpperCase();
        for (const ch of clean) {
            const v = digitValue(ch);
            if (v === -1 || v >= base) {
                return `Символ «${ch}» недопустим для системы счисления с основанием ${base}.`;
            }
        }
        return null;
    }

    function toBase10(str, base) {
        const clean = str.trim().toUpperCase();
        const baseBig = BigInt(base);
        const n = clean.length;

        const terms = [];
        let value = 0n;

        for (let i = 0; i < n; i++) {
            const power = n - 1 - i;
            const dv = BigInt(digitValue(clean[i]));
            const placeValue = baseBig ** BigInt(power);
            const product = dv * placeValue;
            value += product;
            terms.push({ digit: clean[i], power, placeValue, product });
        }

        return { value, terms };
    }

    function fromBase10(value, base) {
        const baseBig = BigInt(base);
        if (value === 0n) return { digits: '0', steps: [] };

        const steps = [];
        let n = value;

        while (n > 0n) {
            const quotient = n / baseBig;
            const remainder = n % baseBig;
            steps.push({ dividend: n, quotient, remainder });
            n = quotient;
        }

        const digits = steps.map(s => digitChar(s.remainder)).reverse().join('');
        return { digits, steps };
    }

    /**
     * Строит многострочную KaTeX-формулу через окружение aligned —
     * общий вид, подстановка цифр, вычисление, результат.
     */
    function renderExpansion(terms, base, value) {
        const general      = terms.map(t => `d_{${t.power}}\\cdot ${base}^{${t.power}}`).join(' + ');
        const substituted   = terms.map(t => `${t.digit}\\cdot ${base}^{${t.power}}`).join(' + ');
        const computed      = terms.map(t => t.product.toString()).join(' + ');

        const latex =
            `\\begin{aligned}` +
            `N &= ${general} \\\\` +
            `&= ${substituted} \\\\` +
            `&= ${computed} \\\\` +
            `&= ${value.toString()}` +
            `\\end{aligned}`;

        return `<div class="ns-katex-block">$$${latex}$$</div>`;
    }

    function renderDivisionSteps(steps, base, digits) {
        const rows = steps.map(s => `
            <div class="ns-div-row">
                <span class="ns-div-math">$${s.dividend.toString()} \\div ${base} = ${s.quotient.toString()}$</span>
                <span class="ns-div-rem">ост. ${s.remainder.toString()}</span>
            </div>
        `).join('');

        const remaindersReversed = steps.map(s => s.remainder.toString()).reverse().join(' ');

        return `
            <div class="ns-div-table">${rows}</div>
            <div class="ns-div-readout">
                Читаем остатки снизу вверх: <span class="ns-readout-digits">${remaindersReversed}</span>
                → <strong>${digits}</strong>
            </div>
        `;
    }

    function convert(inputStr, fromBase, toBase) {
        const err = validate(inputStr, fromBase);
        if (err) return { error: err };

        const clean = inputStr.trim().toUpperCase().replace(/^0+(?=.)/, '');

        let html = '';
        let value;

        if (fromBase !== 10) {
            const { value: v, terms } = toBase10(clean, fromBase);
            value = v;
            html += `
                <div class="ns-step">
                    <p class="ns-step-title">Шаг 1. Переводим в десятичную систему (основание ${fromBase} → 10)</p>
                    ${renderExpansion(terms, fromBase, value)}
                </div>`;
        } else {
            value = BigInt(clean);
        }

        let resultDigits;
        if (toBase !== 10) {
            const { digits, steps } = fromBase10(value, toBase);
            resultDigits = digits;

            if (steps.length > 0) {
                html += `
                    <div class="ns-step">
                        <p class="ns-step-title">
                            ${fromBase !== 10 ? 'Шаг 2. ' : ''}Переводим ${value.toString()} в систему с основанием ${toBase} делением в столбик
                        </p>
                        ${renderDivisionSteps(steps, toBase, digits)}
                    </div>`;
            }
        } else {
            resultDigits = value.toString();
        }

        return { html, result: resultDigits, error: null };
    }

    window.NumberSystemsTool = { convert, validate };
})();