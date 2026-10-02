window.FanoTool = (function () {

    function validateCodes(pairs) {
        const seen = new Set();
        for (const p of pairs) {
            if (!p.symbol || !p.code) return 'Заполните символ и код в каждой строке.';
            if (!/^[01]+$/.test(p.code)) return `Код «${p.code}» должен состоять только из 0 и 1.`;
            if (seen.has(p.symbol)) return `Символ «${p.symbol}» повторяется.`;
            seen.add(p.symbol);
        }
        if (pairs.length < 2) return 'Добавьте хотя бы два символа.';
        return null;
    }

    function checkFano(pairs) {
        const violations = [];
        for (let i = 0; i < pairs.length; i++) {
            for (let j = 0; j < pairs.length; j++) {
                if (i === j) continue;
                if (pairs[j].code.startsWith(pairs[i].code) && pairs[j].code !== pairs[i].code) {
                    violations.push({ prefix: pairs[i], full: pairs[j] });
                }
            }
        }
        return violations;
    }

    /**
     * Строим структуру дерева: узел содержит детей '0' и '1',
     * и, если это лист кода — символ.
     */
    function buildTree(pairs) {
        const root = { children: {} };

        pairs.forEach(p => {
            let node = root;
            for (const bit of p.code) {
                if (!node.children[bit]) node.children[bit] = { children: {} };
                node = node.children[bit];
            }
            node.symbol = p.symbol;
        });

        return root;
    }

    /**
     * Раскладываем дерево в координаты для SVG (простой рекурсивный layout).
     */
    function layoutTree(root) {
        const nodes = [];
        const edges = [];
        let leafCounter = 0;
        const LEVEL_HEIGHT = 70;

        function visit(node, depth, path) {
            const keys = Object.keys(node.children);
            let x;

            if (keys.length === 0) {
                x = leafCounter * 60 + 30;
                leafCounter++;
            } else {
                const childXs = keys.map(k => visit(node.children[k], depth + 1, path + k));
                x = (Math.min(...childXs) + Math.max(...childXs)) / 2;
            }

            const y = depth * LEVEL_HEIGHT + 30;
            nodes.push({ x, y, symbol: node.symbol, isLeaf: keys.length === 0, path });

            keys.forEach(k => {
                edges.push({ from: { x, y }, to: layoutOf(node.children[k]), bit: k });
            });

            return x;
        }

        const posMap = new Map();
        function layoutOf(n) { return posMap.get(n); }

        // Простая версия без карты — пересчитываем позиции явно через два прохода
        function assignX(node, depth, path) {
            const keys = Object.keys(node.children);
            if (keys.length === 0) {
                const x = leafCounter * 70 + 40;
                leafCounter++;
                node._x = x; node._y = depth * 90 + 30; node._path = path;
                return x;
            }
            const xs = keys.map(k => assignX(node.children[k], depth + 1, path + k));
            const x = (Math.min(...xs) + Math.max(...xs)) / 2;
            node._x = x; node._y = depth * 90 + 30; node._path = path;
            return x;
        }

        leafCounter = 0;
        assignX(root, 0, '');

        const outNodes = [];
        const outEdges = [];

        function collect(node) {
            outNodes.push({ x: node._x, y: node._y, symbol: node.symbol, isLeaf: Object.keys(node.children).length === 0 });
            Object.keys(node.children).forEach(k => {
                const child = node.children[k];
                outEdges.push({ x1: node._x, y1: node._y, x2: child._x, y2: child._y, bit: k });
                collect(child);
            });
        }
        collect(root);

        const width  = leafCounter * 70 + 40;
        const height = Math.max(...outNodes.map(n => n.y)) + 50;

        return { nodes: outNodes, edges: outEdges, width, height };
    }

    function renderTreeSvg(pairs) {
        const root = buildTree(pairs);
        const { nodes, edges, width, height } = layoutTree(root);

        let svg = `<svg viewBox="0 0 ${width} ${height}" width="100%" style="max-width:${width}px">`;

        edges.forEach(e => {
            svg += `<line x1="${e.x1}" y1="${e.y1}" x2="${e.x2}" y2="${e.y2}" stroke="#CBD5E1" stroke-width="2"/>`;
            const mx = (e.x1 + e.x2) / 2, my = (e.y1 + e.y2) / 2;
            svg += `<text x="${mx}" y="${my - 6}" font-size="12" fill="#64748B" text-anchor="middle" font-family="JetBrains Mono, monospace">${e.bit}</text>`;
        });

        nodes.forEach(n => {
            if (n.isLeaf && n.symbol) {
                svg += `<circle cx="${n.x}" cy="${n.y}" r="16" fill="#DCFCE7" stroke="#22C55E" stroke-width="2"/>`;
                svg += `<text x="${n.x}" y="${n.y + 5}" font-size="13" font-weight="700" fill="#15803D" text-anchor="middle">${n.symbol}</text>`;
            } else if (n.symbol) {
                // Символ на внутреннем узле — это и есть нарушение условия Фано
                svg += `<circle cx="${n.x}" cy="${n.y}" r="16" fill="#FEE2E2" stroke="#E11D48" stroke-width="2"/>`;
                svg += `<text x="${n.x}" y="${n.y + 5}" font-size="13" font-weight="700" fill="#BE123C" text-anchor="middle">${n.symbol}</text>`;
            } else {
                svg += `<circle cx="${n.x}" cy="${n.y}" r="5" fill="#CBD5E1"/>`;
            }
        });

        svg += '</svg>';
        return svg;
    }

    /**
     * Жадное декодирование: на каждом шаге ищем самый длинный/единственный подходящий код.
     * При выполнении условия Фано подходящий код всегда один — декодирование однозначно.
     */
    function decode(bitString, pairs) {
        const steps = [];
        let pos = 0;
        const s = bitString.trim();

        while (pos < s.length) {
            let matched = null;
            for (const p of pairs) {
                if (s.startsWith(p.code, pos)) { matched = p; break; }
            }
            if (!matched) {
                return { error: `Не удалось разобрать строку начиная с позиции ${pos + 1} ("...${s.slice(pos)}") — нет подходящего кода.`, steps };
            }
            steps.push({ code: matched.code, symbol: matched.symbol, from: pos, to: pos + matched.code.length });
            pos += matched.code.length;
        }

        return { error: null, steps, decoded: steps.map(s => s.symbol).join('') };
    }

    return { validateCodes, checkFano, renderTreeSvg, decode };
})();