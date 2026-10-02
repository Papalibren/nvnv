(function () {
    // Единицы по порядку возрастания. ratio[i] — во сколько раз UNITS[i+1] больше UNITS[i].
    const UNITS = [
        { key: 'bit',  label: 'бит' },
        { key: 'byte', label: 'байт' },
        { key: 'kb',   label: 'КБ' },
        { key: 'mb',   label: 'МБ' },
        { key: 'gb',   label: 'ГБ' },
        { key: 'tb',   label: 'ТБ' },
    ];
    const RATIOS = [8, 1024, 1024, 1024, 1024]; // между соседними единицами

    function indexOf(key) {
        return UNITS.findIndex(u => u.key === key);
    }

    function formatNum(n) {
        const rounded = Math.round(n * 1e6) / 1e6;
        return rounded.toString();
    }

    function convert(valueStr, fromKey, toKey) {
        const raw = valueStr.trim().replace(',', '.');
        if (!raw || isNaN(Number(raw)) || Number(raw) < 0) {
            return { error: 'Введите неотрицательное число.' };
        }
        if (fromKey === toKey) {
            return { error: 'Единицы совпадают — выберите разные.' };
        }

        const fromIdx = indexOf(fromKey);
        const toIdx   = indexOf(toKey);

        let current = Number(raw);
        const steps = [];

        if (fromIdx < toIdx) {
            // Идём вверх по цепочке — делим на каждой ступени
            for (let i = fromIdx; i < toIdx; i++) {
                const ratio = RATIOS[i];
                const next = current / ratio;
                steps.push({
                    title: `${UNITS[i].label} → ${UNITS[i + 1].label}`,
                    formula: `${formatNum(current)} ${UNITS[i].label} ÷ ${ratio} = ${formatNum(next)} ${UNITS[i + 1].label}`,
                });
                current = next;
            }
        } else {
            // Идём вниз по цепочке — умножаем на каждой ступени
            for (let i = fromIdx; i > toIdx; i--) {
                const ratio = RATIOS[i - 1];
                const next = current * ratio;
                steps.push({
                    title: `${UNITS[i].label} → ${UNITS[i - 1].label}`,
                    formula: `${formatNum(current)} ${UNITS[i].label} × ${ratio} = ${formatNum(next)} ${UNITS[i - 1].label}`,
                });
                current = next;
            }
        }

        return { error: null, result: formatNum(current), steps };
    }

    window.BitsBytesTool = { convert, UNITS };
})();