window.TruthTableTool = (function () {

    function tokenize(input) {
        const tokens = [];
        const s = input.trim();
        let i = 0;

        while (i < s.length) {
            const ch = s[i];
            if (/\s/.test(ch)) { i++; continue; }

            if (ch === '(') { tokens.push({ type: 'LPAREN' }); i++; continue; }
            if (ch === ')') { tokens.push({ type: 'RPAREN' }); i++; continue; }

            if (s.startsWith('<->', i) || s.startsWith('<=>', i)) { tokens.push({ type: 'EQV' }); i += 3; continue; }
            if (s.startsWith('->', i) || s.startsWith('=>', i))   { tokens.push({ type: 'IMPL' }); i += 2; continue; }

            if (ch === '¬' || ch === '!') { tokens.push({ type: 'NOT' }); i++; continue; }
            if (ch === '∧' || ch === '&' || ch === '*') { tokens.push({ type: 'AND' }); i++; continue; }
            if (ch === '∨' || ch === '|' || ch === '+') { tokens.push({ type: 'OR' }); i++; continue; }
            if (ch === '⊕' || ch === '^') { tokens.push({ type: 'XOR' }); i++; continue; }
            if (ch === '→') { tokens.push({ type: 'IMPL' }); i++; continue; }
            if (ch === '≡' || ch === '~') { tokens.push({ type: 'EQV' }); i++; continue; }

            if (/[A-Za-zА-Яа-я]/.test(ch)) {
                let j = i;
                while (j < s.length && /[A-Za-zА-Яа-я0-9]/.test(s[j])) j++;
                const word = s.slice(i, j);
                const wordOps = { not: 'NOT', and: 'AND', or: 'OR', xor: 'XOR', impl: 'IMPL', eqv: 'EQV' };
                const key = wordOps[word.toLowerCase()];
                tokens.push(key ? { type: key } : { type: 'VAR', name: word.toUpperCase() });
                i = j;
                continue;
            }

            throw new Error(`Недопустимый символ «${ch}» в выражении.`);
        }
        return tokens;
    }

    function parse(tokens) {
        let pos = 0;
        const peek = () => tokens[pos];
        const consume = (type) => {
            if (!peek() || peek().type !== type) throw new Error('Ошибка в выражении — проверьте скобки и операторы.');
            return tokens[pos++];
        };

        function parseEqv()  { let l = parseImpl(); while (peek()?.type === 'EQV')  { pos++; l = { type: 'eqv',  left: l, right: parseImpl() }; } return l; }
        function parseImpl() { let l = parseOr();   while (peek()?.type === 'IMPL') { pos++; l = { type: 'impl', left: l, right: parseOr() };   } return l; }
        function parseOr()   { let l = parseXor();  while (peek()?.type === 'OR')   { pos++; l = { type: 'or',   left: l, right: parseXor() };  } return l; }
        function parseXor()  { let l = parseAnd();  while (peek()?.type === 'XOR')  { pos++; l = { type: 'xor',  left: l, right: parseAnd() };  } return l; }
        function parseAnd()  { let l = parseNot();  while (peek()?.type === 'AND')  { pos++; l = { type: 'and',  left: l, right: parseNot() };  } return l; }

        function parseNot() {
            if (peek()?.type === 'NOT') { pos++; return { type: 'not', arg: parseNot() }; }
            return parsePrimary();
        }

        function parsePrimary() {
            if (peek()?.type === 'LPAREN') { pos++; const n = parseEqv(); consume('RPAREN'); return n; }
            if (peek()?.type === 'VAR') { return { type: 'var', name: tokens[pos++].name }; }
            throw new Error('Ожидалась переменная или «(».');
        }

        if (tokens.length === 0) throw new Error('Введите логическое выражение.');
        const result = parseEqv();
        if (pos < tokens.length) throw new Error('Лишние символы в конце выражения — проверьте скобки.');
        return result;
    }

    function collectVars(node, set) {
        if (node.type === 'var') { set.add(node.name); return; }
        if (node.type === 'not') { collectVars(node.arg, set); return; }
        collectVars(node.left, set);
        collectVars(node.right, set);
    }

    function evaluate(node, values) {
        switch (node.type) {
            case 'var':  return values[node.name];
            case 'not':  return !evaluate(node.arg, values);
            case 'and':  return evaluate(node.left, values) && evaluate(node.right, values);
            case 'or':   return evaluate(node.left, values) || evaluate(node.right, values);
            case 'xor':  return evaluate(node.left, values) !== evaluate(node.right, values);
            case 'impl': return !evaluate(node.left, values) || evaluate(node.right, values);
            case 'eqv':  return evaluate(node.left, values) === evaluate(node.right, values);
        }
    }

    const PRECEDENCE = { var: 6, and: 4, xor: 3, or: 3, impl: 2, eqv: 1 };
    const SYMBOLS = { and: '\\wedge', or: '\\vee', xor: '\\oplus', impl: '\\rightarrow', eqv: '\\leftrightarrow' };

    // Отрицание рисуем чертой сверху (Ā) — привычная запись из школьных учебников
    function toLatex(node, parentPrec = 0) {
        if (node.type === 'not') {
            return `\\overline{${toLatex(node.arg, 0)}}`;
        }
        if (node.type === 'var') return node.name;

        const prec = PRECEDENCE[node.type];
        const left  = toLatex(node.left, prec);
        const right = toLatex(node.right, prec + 1);
        const str = `${left} ${SYMBOLS[node.type]} ${right}`;

        return prec < parentPrec ? `(${str})` : str;
    }

    function nodeKey(node) {
        if (node.type === 'var') return 'v:' + node.name;
        if (node.type === 'not') return 'n:' + nodeKey(node.arg);
        return `${node.type}:${nodeKey(node.left)}:${nodeKey(node.right)}`;
    }

    function collectSubexpressions(node, list, seen) {
        if (node.type === 'var') return;
        if (node.type === 'not') collectSubexpressions(node.arg, list, seen);
        else { collectSubexpressions(node.left, list, seen); collectSubexpressions(node.right, list, seen); }

        const key = nodeKey(node);
        if (!seen.has(key)) { seen.add(key); list.push(node); }
    }

    function build(expression) {
        const ast = parse(tokenize(expression));

        const varsSet = new Set();
        collectVars(ast, varsSet);
        const vars = Array.from(varsSet).sort();

        if (vars.length === 0) throw new Error('В выражении не найдено ни одной переменной.');
        if (vars.length > 6) throw new Error('Слишком много переменных (максимум 6) — таблица будет чересчур большой.');

        const subList = [];
        collectSubexpressions(ast, subList, new Set());

        const columns = vars.map(v => ({ label: v, isResult: false }))
            .concat(subList.map(node => ({ label: toLatex(node, 0), isResult: node === ast })));

        const n = vars.length;
        const rows = [];

        for (let i = 0; i < (1 << n); i++) {
            const values = {};
            vars.forEach((v, idx) => { values[v] = Boolean((i >> (n - 1 - idx)) & 1); });

            const rowValues = vars.map(v => (values[v] ? 1 : 0))
                .concat(subList.map(node => (evaluate(node, values) ? 1 : 0)));

            rows.push(rowValues);
        }

        return { vars, columns, rows };
    }

    return { build };
})();