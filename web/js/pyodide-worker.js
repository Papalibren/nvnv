if (typeof importScripts !== 'function') {
    console.error('pyodide-worker.js загружен НЕ как Web Worker.');
    throw new Error('pyodide-worker.js must run inside a Worker context');
}

importScripts('https://cdn.jsdelivr.net/pyodide/v0.26.4/full/pyodide.js');

let pyodideInst = null;
let controlView = null;
let dataView = null;
let jediReady = false;

self.onmessage = async (e) => {
    const msg = e.data;

    if (msg.type === 'init') {
        if (msg.controlSAB && msg.dataSAB) {
            controlView = new Int32Array(msg.controlSAB);
            dataView = new Uint8Array(msg.dataSAB);
        }
        pyodideInst = await loadPyodide({
            indexURL: 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/',
        });
        self.postMessage({ type: 'ready' });

        setupJedi();
        return;
    }

    if (msg.type === 'run') {
        await runCode(msg.code, msg.id);
        return;
    }

    if (msg.type === 'complete') {
        const list = await getCompletions(msg.code, msg.line, msg.column);
        self.postMessage({ type: 'completions', id: msg.id, list });
        return;
    }
};

async function setupJedi() {
    try {
        await pyodideInst.loadPackage('micropip');
        const micropip = pyodideInst.pyimport('micropip');
        await micropip.install('jedi');
        jediReady = true;
        self.postMessage({ type: 'jedi-ready' });
    } catch (e) {
        jediReady = false;
        self.postMessage({ type: 'jedi-failed', error: String(e && e.message ? e.message : e) });
    }
}

async function getCompletions(code, line, column) {
    if (!jediReady) return [];

    try {
        pyodideInst.globals.set('__complete_code', code);
        pyodideInst.globals.set('__complete_line', line);
        pyodideInst.globals.set('__complete_col', column);

        const result = pyodideInst.runPython(`
import jedi
import traceback

__debug_error = None
__result = []
try:
    __script = jedi.Script(code=__complete_code)
    __completions = __script.complete(line=__complete_line, column=__complete_col)
    __result = [(c.name, c.type) for c in __completions[:20]]
except Exception as __e:
    __debug_error = traceback.format_exc()

(__result, __debug_error)
`);

        const [arr, errorText] = result.toJs();
        result.destroy();

        if (errorText) {
            self.postMessage({ type: 'completion-debug-error', error: errorText });
            return [];
        }

        return arr.map(([name, type]) => ({ name, type }));
    } catch (e) {
        self.postMessage({ type: 'completion-debug-error', error: String(e && e.message ? e.message : e) });
        return [];
    }
}

function blockingInput(promptText) {
    if (promptText) {
        self.postMessage({ type: 'stdout', text: promptText });
    }

    if (!controlView) {
        self.postMessage({ type: 'no-input-support' });
        return '';
    }

    self.postMessage({ type: 'input-request' });

    Atomics.store(controlView, 0, 0);
    Atomics.wait(controlView, 0, 0);

    const len = controlView[1];
    const bytes = dataView.slice(0, len);
    return new TextDecoder().decode(bytes);
}

function cleanTraceback(e) {
    const text = String(e && e.message ? e.message : e || '').trim();
    if (!text) return 'Произошла неизвестная ошибка.';

    const lines = text.split('\n').filter((line) => {
        return !/site-packages|pyodide\.asm|runPythonAsync|await |<string>|micropip|asyncio\/|pyodide\.js/i.test(line);
    });

    return lines.join('\n').replace(/File "<exec>"/g, 'Ваш код').trim() || text;
}

async function runCode(code, id) {
    pyodideInst.setStdout({ batched: (s) => { self.postMessage({ type: 'stdout', text: s + '\n' }); } });
    pyodideInst.setStderr({ batched: (s) => { self.postMessage({ type: 'stderr', text: s + '\n' }); } });

    const namespace = pyodideInst.globals.get('dict')();
    namespace.set('__blocking_input_js', blockingInput);

    const preamble =
`import builtins as __b
def __mock_input(prompt=''):
    return __blocking_input_js(prompt)
__b.input = __mock_input
`;

    try {
        await pyodideInst.runPythonAsync(preamble + '\n' + code, { globals: namespace });
    } catch (err) {
        self.postMessage({ type: 'stderr', text: cleanTraceback(err) });
    } finally {
        namespace.destroy();
    }

    self.postMessage({ type: 'done', id });
}