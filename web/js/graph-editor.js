(function () {
    const NS = 'http://www.w3.org/2000/svg';
    const W = 700, H = 460;

    let svg, out, vList, eList, hint, matrixBox, distFromSelect, distToSelect, distResultBox;
    let vertices = [];  // {id, label, x, y}
    let edges = [];     // [idA, idB]
    let nextId = 1;
    let mode = 'vertex';
    let edgeStart = null;
    let drag = null;

    const HINTS = {
        vertex: 'Клик по свободному месту добавляет вершину. Двойной клик по вершине — переименовать.',
        edge: 'Клик по первой вершине, затем по второй — создастся ребро.',
        move: 'Перетаскивание вершин мышью.',
        delete: 'Клик по вершине удаляет её вместе с рёбрами, клик по линии удаляет ребро.',
    };

    function getSettings() {
        return {
            pointRadius: +document.getElementById('optRadius').value || 4,
            strokeWidth: +document.getElementById('optStroke').value || 1.5,
            fontSize: +document.getElementById('optFont').value || 16,
        };
    }

    function getVertex(id) { return vertices.find(v => v.id === id); }

    function nextLabel() {
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const used = new Set(vertices.map(v => v.label));
        for (const ch of letters) if (!used.has(ch)) return ch;
        let i = 1;
        while (used.has('V' + i)) i++;
        return 'V' + i;
    }

    function addVertex(x, y) {
        vertices.push({ id: nextId++, label: nextLabel(), x: Math.round(x), y: Math.round(y) });
    }

    function addEdge(a, b) {
        if (a === b) return;
        if (edges.some(ed => (ed[0] === a && ed[1] === b) || (ed[0] === b && ed[1] === a))) return;
        edges.push([a, b]);
    }

    function removeVertex(id) {
        vertices = vertices.filter(v => v.id !== id);
        edges = edges.filter(ed => ed[0] !== id && ed[1] !== id);
        if (edgeStart === id) edgeStart = null;
    }

    function labelPos(v) {
        const s = getSettings();
        let cx = 0, cy = 0;
        vertices.forEach(p => { cx += p.x; cy += p.y; });
        cx /= vertices.length; cy /= vertices.length;
        let dx = v.x - cx, dy = v.y - cy;
        const len = Math.hypot(dx, dy);
        if (len < 10) { dx = 0; dy = -1; } else { dx /= len; dy /= len; }
        const off = s.fontSize * 0.9 + 6;
        return { x: v.x + dx * off, y: v.y + dy * off + s.fontSize * 0.35 };
    }

    const r1 = n => Math.round(n * 10) / 10;
    const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const clamp = (v, max) => Math.max(10, Math.min(max - 10, v));

    function toSVGPoint(e) {
        const pt = svg.createSVGPoint();
        pt.x = e.clientX; pt.y = e.clientY;
        return pt.matrixTransform(svg.getScreenCTM().inverse());
    }

    function render() {
        const s = getSettings();
        svg.innerHTML = '';

        edges.forEach((ed, i) => {
            const a = getVertex(ed[0]), b = getVertex(ed[1]);
            if (!a || !b) return;
            const line = document.createElementNS(NS, 'line');
            line.setAttribute('x1', a.x); line.setAttribute('y1', a.y);
            line.setAttribute('x2', b.x); line.setAttribute('y2', b.y);
            line.setAttribute('stroke', '#0F172A');
            line.setAttribute('stroke-width', s.strokeWidth);
            svg.appendChild(line);

            const hitLine = document.createElementNS(NS, 'line');
            hitLine.setAttribute('x1', a.x); hitLine.setAttribute('y1', a.y);
            hitLine.setAttribute('x2', b.x); hitLine.setAttribute('y2', b.y);
            hitLine.setAttribute('stroke', 'transparent');
            hitLine.setAttribute('stroke-width', 12);
            hitLine.dataset.edge = i;
            hitLine.style.cursor = mode === 'delete' ? 'pointer' : 'default';
            svg.appendChild(hitLine);
        });

        vertices.forEach(v => {
            const c = document.createElementNS(NS, 'circle');
            c.setAttribute('cx', v.x); c.setAttribute('cy', v.y);
            c.setAttribute('r', s.pointRadius);
            c.setAttribute('fill', '#4F46E5');
            if (v.id === edgeStart) {
                c.setAttribute('stroke', '#E11D48');
                c.setAttribute('stroke-width', 2);
            }
            svg.appendChild(c);

            const lp = labelPos(v);
            const t = document.createElementNS(NS, 'text');
            t.setAttribute('x', r1(lp.x)); t.setAttribute('y', r1(lp.y));
            t.setAttribute('font-size', s.fontSize);
            t.setAttribute('font-family', 'Inter, Arial, sans-serif');
            t.setAttribute('font-weight', '600');
            t.setAttribute('fill', '#0F172A');
            t.setAttribute('text-anchor', 'middle');
            t.style.pointerEvents = 'none';
            t.textContent = v.label;
            svg.appendChild(t);

            const hitC = document.createElementNS(NS, 'circle');
            hitC.setAttribute('cx', v.x); hitC.setAttribute('cy', v.y);
            hitC.setAttribute('r', 12);
            hitC.setAttribute('fill', 'transparent');
            hitC.dataset.vertex = v.id;
            hitC.style.cursor = mode === 'move' ? 'move' : 'pointer';
            svg.appendChild(hitC);
        });
    }

    function computeBounds() {
        const s = getSettings();
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

        vertices.forEach(v => {
            minX = Math.min(minX, v.x - s.pointRadius);
            maxX = Math.max(maxX, v.x + s.pointRadius);
            minY = Math.min(minY, v.y - s.pointRadius);
            maxY = Math.max(maxY, v.y + s.pointRadius);

            const lp = labelPos(v);
            const halfW = v.label.length * s.fontSize * 0.35;
            minX = Math.min(minX, lp.x - halfW);
            maxX = Math.max(maxX, lp.x + halfW);
            minY = Math.min(minY, lp.y - s.fontSize);
            maxY = Math.max(maxY, lp.y + s.fontSize * 0.25);
        });

        return { minX, minY, maxX, maxY };
    }

    function buildSVG() {
        const s = getSettings();
        const crop = document.getElementById('optCrop').checked;

        let ox = 0, oy = 0, w = W, h = H;
        if (crop && vertices.length > 0) {
            const pad = 10;
            const b = computeBounds();
            ox = b.minX - pad;
            oy = b.minY - pad;
            w = Math.ceil(b.maxX - b.minX + pad * 2);
            h = Math.ceil(b.maxY - b.minY + pad * 2);
        }

        const X = x => r1(x - ox);
        const Y = y => r1(y - oy);

        const lines = [];
        lines.push(`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">`);
        if (document.getElementById('optBg').checked) {
            lines.push(`  <rect width="${w}" height="${h}" fill="white"/>`);
        }
        edges.forEach(ed => {
            const a = getVertex(ed[0]), b = getVertex(ed[1]);
            if (!a || !b) return;
            lines.push(`  <line x1="${X(a.x)}" y1="${Y(a.y)}" x2="${X(b.x)}" y2="${Y(b.y)}" stroke="black" stroke-width="${s.strokeWidth}"/>`);
        });
        vertices.forEach(v => {
            lines.push(`  <circle cx="${X(v.x)}" cy="${Y(v.y)}" r="${s.pointRadius}" fill="black"/>`);
        });
        vertices.forEach(v => {
            const lp = labelPos(v);
            lines.push(`  <text x="${X(lp.x)}" y="${Y(lp.y)}" font-family="Arial, sans-serif" font-size="${s.fontSize}" text-anchor="middle">${esc(v.label)}</text>`);
        });
        lines.push('</svg>');
        return lines.join('\n');
    }

    // ============ Таблица смежности ============
    function buildAdjacency() {
        const n = vertices.length;
        const idx = new Map(vertices.map((v, i) => [v.id, i]));
        const matrix = Array.from({ length: n }, () => Array(n).fill(0));

        edges.forEach(([a, b]) => {
            const i = idx.get(a), j = idx.get(b);
            if (i !== undefined && j !== undefined) { matrix[i][j] = 1; matrix[j][i] = 1; }
        });

        return { labels: vertices.map(v => v.label), matrix };
    }

    function renderMatrix() {
        if (vertices.length === 0) {
            matrixBox.innerHTML = '<p class="text-sm text-base-400">Добавьте вершины, чтобы увидеть таблицу смежности.</p>';
            return;
        }

        const { labels, matrix } = buildAdjacency();

        let html = '<table class="graph-matrix"><thead><tr><th></th>';
        labels.forEach(l => { html += `<th>${esc(l)}</th>`; });
        html += '</tr></thead><tbody>';

        labels.forEach((l, i) => {
            html += `<tr><th>${esc(l)}</th>`;
            matrix[i].forEach(v => {
                html += `<td class="${v ? 'graph-matrix-on' : ''}">${v}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';

        matrixBox.innerHTML = html;
    }

    // ============ Расстояния и маршруты ============
    function buildAdjacencyList() {
        const list = new Map();
        vertices.forEach(v => list.set(v.id, []));
        edges.forEach(([a, b]) => {
            if (list.has(a)) list.get(a).push(b);
            if (list.has(b)) list.get(b).push(a);
        });
        return list;
    }

    function bfsShortestPath(fromId, toId) {
        const adj = buildAdjacencyList();
        const visited = new Set([fromId]);
        const prev = new Map();
        const queue = [fromId];

        while (queue.length) {
            const cur = queue.shift();
            if (cur === toId) break;
            for (const next of (adj.get(cur) || [])) {
                if (!visited.has(next)) {
                    visited.add(next);
                    prev.set(next, cur);
                    queue.push(next);
                }
            }
        }

        if (!visited.has(toId)) return null;

        const path = [toId];
        let cur = toId;
        while (cur !== fromId) {
            cur = prev.get(cur);
            path.unshift(cur);
        }
        return path;
    }

    function countAllSimplePaths(fromId, toId, limit = 500) {
        const adj = buildAdjacencyList();
        let count = 0;
        let truncated = false;
        const visited = new Set([fromId]);

        function dfs(cur) {
            if (count >= limit) { truncated = true; return; }
            if (cur === toId) { count++; return; }
            for (const next of (adj.get(cur) || [])) {
                if (!visited.has(next)) {
                    visited.add(next);
                    dfs(next);
                    visited.delete(next);
                    if (count >= limit) return;
                }
            }
        }

        dfs(fromId);
        return { count, truncated };
    }

    function populateDistanceSelects() {
        const opts = vertices.map(v => `<option value="${v.id}">${esc(v.label)}</option>`).join('');
        distFromSelect.innerHTML = opts;
        distToSelect.innerHTML = opts;
    }

    function calculateDistance() {
        const fromId = +distFromSelect.value;
        const toId = +distToSelect.value;

        if (!fromId || !toId) {
            distResultBox.innerHTML = '<p class="text-sm text-base-400">Выберите обе вершины.</p>';
            return;
        }
        if (fromId === toId) {
            distResultBox.innerHTML = '<p class="text-sm text-base-400">Выберите разные вершины.</p>';
            return;
        }

        const path = bfsShortestPath(fromId, toId);

        if (!path) {
            distResultBox.innerHTML = '<div class="alert-error">Между вершинами нет пути.</div>';
            return;
        }

        const pathLabels = path.map(id => getVertex(id).label).join(' → ');
        const { count, truncated } = vertices.length <= 14
            ? countAllSimplePaths(fromId, toId)
            : { count: null, truncated: false };

        distResultBox.innerHTML = `
            <div class="graph-dist-row"><span>Кратчайшее расстояние</span><strong>${path.length - 1} ребро(-ер)</strong></div>
            <div class="graph-dist-row"><span>Пример кратчайшего пути</span><strong class="font-mono">${esc(pathLabels)}</strong></div>
            <div class="graph-dist-row"><span>Всего различных маршрутов (без повторов вершин)</span>
                <strong>${count === null ? 'граф слишком большой для полного перебора' : (count + (truncated ? '+' : ''))}</strong>
            </div>
        `;
    }

    // ============ Списки, вывод, сохранение ============
    function updateOutput() {
        out.value = buildSVG();

        vList.innerHTML = vertices.map(v =>
            `<div class="graph-list-row"><span>${esc(v.label)} (${v.x}, ${v.y})</span><button data-del-v="${v.id}">✕</button></div>`
        ).join('');

        eList.innerHTML = edges.map((ed, i) => {
            const a = getVertex(ed[0]), b = getVertex(ed[1]);
            if (!a || !b) return '';
            return `<div class="graph-list-row"><span>${esc(a.label)} — ${esc(b.label)}</span><button data-del-e="${i}">✕</button></div>`;
        }).join('');

        renderMatrix();
        populateDistanceSelects();
        save();
    }

    function save() {
        try {
            localStorage.setItem('ege-graph', JSON.stringify({ vertices, edges, nextId }));
        } catch (e) {}
    }

    function load() {
        try {
            const d = JSON.parse(localStorage.getItem('ege-graph'));
            if (d && Array.isArray(d.vertices) && d.vertices.length) {
                vertices = d.vertices;
                edges = d.edges || [];
                nextId = d.nextId || vertices.length + 1;
                return true;
            }
        } catch (e) {}
        return false;
    }

    function loadExample() {
        vertices = [
            { id: 1, label: 'C', x: 160, y: 0 },
            { id: 2, label: 'B', x: 620, y: 40 },
            { id: 3, label: 'F', x: 100,  y: 220 },
            { id: 4, label: 'D', x: 380, y: 160 },
            { id: 5, label: 'E', x: 580, y: 270 },
            { id: 6, label: 'A', x: 290, y: 370 },
        ];
        edges = [[1,2],[1,3],[3,4],[4,2],[4,5],[2,5],[3,6],[6,5]];
        nextId = 7;
        edgeStart = null;
    }

    function setMode(m) {
        mode = m;
        edgeStart = null;
        document.querySelectorAll('.graph-mode-btn').forEach(b => {
            b.classList.toggle('is-active', b.dataset.mode === m);
        });
        hint.textContent = HINTS[m];
        svg.style.cursor = m === 'vertex' ? 'crosshair' : 'default';
        render();
    }

    function downloadPng() {
        const svgText = buildSVG();
        const svgBlob = new Blob([svgText], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(svgBlob);
        const img = new Image();

        img.onload = () => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(svgText, 'image/svg+xml');
            const w = parseInt(doc.documentElement.getAttribute('width'), 10) || W;
            const h = parseInt(doc.documentElement.getAttribute('height'), 10) || H;

            const canvas = document.createElement('canvas');
            const scale = 2; // для чёткости при печати
            canvas.width = w * scale;
            canvas.height = h * scale;
            const ctx = canvas.getContext('2d');
            ctx.scale(scale, scale);
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, w, h);
            ctx.drawImage(img, 0, 0, w, h);

            canvas.toBlob(blob => {
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'graph.png';
                a.click();
                URL.revokeObjectURL(url);
            });
        };

        img.src = url;
    }

    function init() {
        svg      = document.getElementById('graph-canvas');
        out      = document.getElementById('graph-svg-out');
        vList    = document.getElementById('graph-v-list');
        eList    = document.getElementById('graph-e-list');
        hint     = document.getElementById('graph-hint');
        matrixBox = document.getElementById('graph-matrix-box');
        distFromSelect = document.getElementById('graph-dist-from');
        distToSelect   = document.getElementById('graph-dist-to');
        distResultBox  = document.getElementById('graph-dist-result');

        svg.addEventListener('pointerdown', e => {
            const pt = toSVGPoint(e);
            const vId = e.target.dataset.vertex;
            const eIdx = e.target.dataset.edge;

            if (vId !== undefined) {
                const id = +vId;
                if (mode === 'edge') {
                    if (edgeStart === null) edgeStart = id;
                    else if (edgeStart !== id) { addEdge(edgeStart, id); edgeStart = null; }
                    else edgeStart = null;
                    render(); updateOutput();
                } else if (mode === 'move') {
                    const v = getVertex(id);
                    drag = { id, dx: v.x - pt.x, dy: v.y - pt.y };
                    svg.setPointerCapture(e.pointerId);
                } else if (mode === 'delete') {
                    removeVertex(id);
                    render(); updateOutput();
                }
            } else if (eIdx !== undefined) {
                if (mode === 'delete') {
                    edges.splice(+eIdx, 1);
                    render(); updateOutput();
                }
            } else {
                if (mode === 'vertex') {
                    addVertex(clamp(pt.x, W), clamp(pt.y, H));
                    render(); updateOutput();
                } else if (mode === 'edge') {
                    edgeStart = null;
                    render();
                }
            }
        });

        svg.addEventListener('pointermove', e => {
            if (!drag) return;
            const pt = toSVGPoint(e);
            const v = getVertex(drag.id);
            v.x = Math.round(clamp(pt.x + drag.dx, W));
            v.y = Math.round(clamp(pt.y + drag.dy, H));
            render();
        });

        window.addEventListener('pointerup', () => { if (drag) { drag = null; updateOutput(); } });

        svg.addEventListener('dblclick', e => {
            const vId = e.target.dataset.vertex;
            if (vId === undefined) return;
            const v = getVertex(+vId);
            const name = prompt('Новая метка вершины:', v.label);
            if (name && name.trim()) { v.label = name.trim().toUpperCase(); render(); updateOutput(); }
        });

        document.querySelectorAll('.graph-mode-btn').forEach(b => {
            b.addEventListener('click', () => setMode(b.dataset.mode));
        });

        vList.addEventListener('click', e => {
            const id = e.target.dataset.delV;
            if (id !== undefined) { removeVertex(+id); render(); updateOutput(); }
        });
        eList.addEventListener('click', e => {
            const i = e.target.dataset.delE;
            if (i !== undefined) { edges.splice(+i, 1); render(); updateOutput(); }
        });

        ['optRadius', 'optStroke', 'optFont', 'optBg', 'optCrop'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => { render(); updateOutput(); });
        });

        document.getElementById('graph-btn-download-svg').addEventListener('click', () => {
            const blob = new Blob([out.value], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'graph.svg'; a.click();
            URL.revokeObjectURL(url);
        });

        document.getElementById('graph-btn-download-png').addEventListener('click', downloadPng);

        document.getElementById('graph-btn-example').addEventListener('click', () => {
            if (vertices.length && !confirm('Загрузить пример поверх текущего графа?')) return;
            loadExample(); render(); updateOutput();
        });

        document.getElementById('graph-btn-clear').addEventListener('click', () => {
            if (!confirm('Очистить всё?')) return;
            vertices = []; edges = []; nextId = 1; edgeStart = null;
            render(); updateOutput();
        });

        document.getElementById('graph-dist-calc').addEventListener('click', calculateDistance);

        if (!load()) loadExample();
        setMode('vertex');
        render();
        updateOutput();
    }

    document.addEventListener('DOMContentLoaded', init);
})();