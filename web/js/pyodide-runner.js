window.PyRunner = (function () {
    let worker = null;
    let controlSAB = null;
    let dataSAB = null;
    let controlView = null;
    let dataView = null;
    let status = 'idle';
    let jediStatus = 'loading';
    let runId = 0;
    let pendingRunResolvers = new Map();
    let pendingCompletionResolvers = new Map();
    let activeHandlers = null;
    let busy = false;

    function setStatus(newStatus) {
        status = newStatus;
        console.log('[PyRunner] status =', status);
        document.dispatchEvent(new CustomEvent('pyodide:status', { detail: { status } }));
    }

    function supportsSharedArrayBuffer() {
        return typeof SharedArrayBuffer !== 'undefined' && window.crossOriginIsolated === true;
    }

    function initWorker() {
        if (worker) return;
        setStatus('loading');

        worker = new Worker('/js/pyodide-worker.js');

        if (supportsSharedArrayBuffer()) {
            controlSAB = new SharedArrayBuffer(8);
            dataSAB = new SharedArrayBuffer(2048);
            controlView = new Int32Array(controlSAB);
            dataView = new Uint8Array(dataSAB);
        }

        worker.onmessage = (e) => {
            const msg = e.data;
            console.log('[PyRunner] <- worker message:', msg.type, msg);

            if (msg.type === 'ready') { setStatus('ready'); return; }
            if (msg.type === 'jedi-ready') { jediStatus = 'ready'; console.log('[PyRunner] jediStatus = ready'); return; }
            if (msg.type === 'jedi-failed') { jediStatus = 'unavailable'; console.log('[PyRunner] jediStatus = unavailable', msg.error); return; }
            if (msg.type === 'completion-debug-error') { console.error('[PyRunner] jedi completion error:\n', msg.error); return; }
            if (msg.type === 'stdout' || msg.type === 'stderr') {
                if (activeHandlers && activeHandlers.onOutput) activeHandlers.onOutput(msg.text);
                return;
            }

            if (msg.type === 'input-request') {
                if (activeHandlers && activeHandlers.onInputRequest) activeHandlers.onInputRequest();
                return;
            }

            if (msg.type === 'no-input-support') {
                if (activeHandlers && activeHandlers.onOutput) {
                    activeHandlers.onOutput('\n[Интерактивный ввод недоступен в этом браузере — input() вернул пустую строку]\n');
                }
                return;
            }

            if (msg.type === 'done') {
                busy = false;
                const resolve = pendingRunResolvers.get(msg.id);
                if (resolve) { resolve(); pendingRunResolvers.delete(msg.id); }
                return;
            }

            if (msg.type === 'completions') {
                const resolve = pendingCompletionResolvers.get(msg.id);
                if (resolve) { resolve(msg.list); pendingCompletionResolvers.delete(msg.id); }
                return;
            }
        };

        worker.onerror = (err) => {
            console.error('[PyRunner] worker onerror:', err.message, err);
        };

        worker.postMessage({ type: 'init', controlSAB, dataSAB });
    }

    function preload() {
        initWorker();
    }

    function getStatus() { return status; }
    function isBusy() { return busy; }

    function submitInput(text) {
        if (!controlView) return;
        const bytes = new TextEncoder().encode(text);
        dataView.set(bytes);
        controlView[1] = bytes.length;
        Atomics.store(controlView, 0, 1);
        Atomics.notify(controlView, 0);
    }

    function run(code, handlers = {}) {
        initWorker();
        activeHandlers = handlers;
        busy = true;

        return new Promise((resolve) => {
            const id = ++runId;
            pendingRunResolvers.set(id, resolve);

            const send = () => worker.postMessage({ type: 'run', code, id });

            if (status === 'ready') {
                send();
            } else {
                const onReady = (e) => {
                    if (e.detail.status === 'ready') {
                        document.removeEventListener('pyodide:status', onReady);
                        send();
                    }
                };
                document.addEventListener('pyodide:status', onReady);
            }
        });
    }

    function getCompletions(code, line, column) {
        console.log('[PyRunner] getCompletions called. status=', status, 'busy=', busy, 'jediStatus=', jediStatus);

        if (status !== 'ready' || busy || jediStatus !== 'ready') {
            console.log('[PyRunner] getCompletions -> short-circuit empty (see conditions above)');
            return Promise.resolve([]);
        }

        return new Promise((resolve) => {
            const id = 'c' + (++runId);
            let settled = false;

            const timer = setTimeout(() => {
                if (settled) return;
                settled = true;
                pendingCompletionResolvers.delete(id);
                console.log('[PyRunner] getCompletions TIMEOUT for id', id);
                resolve([]);
            }, 1500);

            pendingCompletionResolvers.set(id, (list) => {
                if (settled) return;
                settled = true;
                clearTimeout(timer);
                console.log('[PyRunner] getCompletions resolved with', list);
                resolve(list);
            });

            worker.postMessage({ type: 'complete', code, line, column, id });
        });
    }

    return { run, preload, getStatus, isBusy, submitInput, supportsSharedArrayBuffer, getCompletions };
})();