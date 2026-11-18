const CANAL = 'laravel-bar-pwa';
const PROBE_TIMEOUT_MS = 2500;

let estado = typeof navigator !== 'undefined' ? navigator.onLine !== false : true;
let probeEnCurso = null;
let canal = null;
let inicializado = false;

function despachar(tipo, detalle, difundir) {
    window.dispatchEvent(new CustomEvent(tipo, { detail: detalle }));
    if (difundir && canal) canal.postMessage({ tipo: tipo, detalle: detalle });
}

function fijarEstado(nuevo, difundir) {
    if (nuevo === estado) return;
    estado = nuevo;
    despachar(nuevo ? 'app:online' : 'app:offline', { online: nuevo }, difundir);
}

export function isOnline() {
    return estado;
}

export function publicarEvento(tipo, detalle) {
    despachar(tipo, detalle || {}, true);
}

export function probeOnline() {
    if (probeEnCurso) return probeEnCurso;
    probeEnCurso = new Promise((resolve) => {
        const controlador = typeof AbortController !== 'undefined' ? new AbortController() : null;
        const temporizador = setTimeout(function () {
            if (controlador) controlador.abort();
        }, PROBE_TIMEOUT_MS);

        fetch('/ping', {
            method: 'GET',
            cache: 'no-store',
            signal: controlador ? controlador.signal : undefined,
        })
            .then((res) => {
                clearTimeout(temporizador);
                fijarEstado(!!res.ok, true);
                resolve(!!res.ok);
            })
            .catch(() => {
                clearTimeout(temporizador);
                fijarEstado(false, true);
                resolve(false);
            });
    }).then((ok) => {
        probeEnCurso = null;
        return ok;
    });
    return probeEnCurso;
}

export function onConnectionChange(callback) {
    window.addEventListener('app:online', callback);
    window.addEventListener('app:offline', callback);
}

export function onDataRefreshed(callback) {
    window.addEventListener('app:data-refreshed', callback);
}

export function initConnection() {
    if (inicializado) return;
    inicializado = true;

    if (typeof BroadcastChannel !== 'undefined') {
        canal = new BroadcastChannel(CANAL);
        canal.onmessage = (evento) => {
            const mensaje = evento.data || {};
            if (mensaje.tipo === 'app:online' || mensaje.tipo === 'app:offline') {
                estado = !!mensaje.detalle.online;
                despachar(mensaje.tipo, mensaje.detalle, false);
            } else if (mensaje.tipo) {
                despachar(mensaje.tipo, mensaje.detalle || {}, false);
            }
        };
    }

    window.addEventListener('online', () => probeOnline());
    window.addEventListener('offline', () => fijarEstado(false, true));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') probeOnline();
    });

    if (estado) probeOnline();
}
