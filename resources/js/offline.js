import db from './db';
import { isOnline, probeOnline, initConnection, publicarEvento, onConnectionChange } from './connection';

const MAX_INTENTOS = 5;

let isSyncing = false;
let csrfToken = '';

export { isOnline, probeOnline };

export function getCSRF() {
    return csrfToken;
}

export function setCSRF(token) {
    csrfToken = token;
}

function baseHeaders() {
    const h = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    if (csrfToken) h['X-CSRF-TOKEN'] = csrfToken;
    return h;
}

function notifyChange() {
    if (window.__offlineOnChange) window.__offlineOnChange();
}

function refrescarCSRF() {
    return fetch('/api/offline/csrf', {
        headers: { Accept: 'application/json' },
        cache: 'no-store',
    })
        .then((res) => (res.ok ? res.json() : null))
        .then((data) => {
            if (data && data.csrf_token) {
                setCSRF(data.csrf_token);
                return true;
            }
            return false;
        })
        .catch(() => false);
}

function esSessionExpirada(res) {
    return res.status === 419 || (res.redirected && /\/login(\?|$)/.test(res.url));
}

function encolar(op) {
    return db.putOp(op).then(() => {
        notifyChange();
        scheduleSync();
    });
}

export function enqueuePedido(pedido) {
    return encolar({ type: 'pedido', ...pedido });
}

export function enqueuePedidoEstado(pedidoId, estado, estadoAnterior) {
    return encolar({
        type: 'pedido_estado',
        pedido_id: pedidoId,
        estado: estado,
        estado_anterior: estadoAnterior || null,
    });
}

export function enqueuePagoMesa(mesaId, datos) {
    return encolar({
        type: 'pago_mesa',
        mesa_id: mesaId,
        metodo_pago: datos.metodo_pago,
        modo: datos.modo,
        lineas: datos.lineas || [],
    });
}

export function enqueueCerrarMesa(mesaId) {
    return encolar({ type: 'cerrar_mesa', mesa_id: mesaId });
}

export function scheduleSync(delay) {
    setTimeout(() => {
        syncNow().catch(() => notifyChange());
    }, delay || 0);
}

export function syncNow(forzar) {
    if (isSyncing) return Promise.resolve();
    if (!isOnline()) {
        notifyChange();
        return Promise.resolve();
    }
    isSyncing = true;
    return db.getAllOps()
        .then((ops) => {
            const enviables = ops.filter((op) => forzar || (op.attempts || 0) < MAX_INTENTOS);
            if (!enviables.length) return [];
            return sendBatch(enviables);
        })
        .catch((err) => {
            if (err && (err.code === 'session_expired' || err.status === 419)) {
                return refrescarCSRF().then((ok) => {
                    if (!ok) {
                        publicarEvento('app:sesion-expirada');
                        throw err;
                    }
                    return db.getAllOps().then((ops) => {
                        const enviables = ops.filter((op) => forzar || (op.attempts || 0) < MAX_INTENTOS);
                        return enviables.length ? sendBatch(enviables) : [];
                    });
                });
            }
            throw err;
        })
        .then(() => notifyChange())
        .catch((err) => {
            notifyChange();
            throw err;
        })
        .finally(() => {
            isSyncing = false;
        });
}

function sendBatch(ops) {
    const payload = ops.map((op) => ({ ...op, op }));
    return fetch('/api/offline/sync', {
        method: 'POST',
        headers: baseHeaders(),
        body: JSON.stringify({ ops: payload }),
    })
        .then((res) => {
            if (esSessionExpirada(res)) {
                const err = new Error('session_expired');
                err.code = 'session_expired';
                err.status = res.status;
                throw err;
            }
            if (!res.ok) {
                const err = new Error('sync_failed_' + res.status);
                err.status = res.status;
                throw err;
            }
            return res.json();
        })
        .then((data) => {
            const results = data.results || [];
            const fatal = results.filter((r) => !r.ok && r.fatal).map((r) => r.client_id);
            const removals = results.filter((r) => r.ok).map((r) => r.client_id);
            const failed = results.filter((r) => !r.ok && !r.fatal);
            const opsIndex = {};
            ops.forEach((o) => { opsIndex[o.client_id] = o; });

            const syncPromises = removals.map((cid) => {
                const op = opsIndex[cid];
                if (op && op.type === 'pedido') {
                    const resultado = results.find((r) => r.client_id === cid) || {};
                    if (resultado.server_id) return db.putSyncedPedido(cid, resultado.server_id);
                }
                return Promise.resolve();
            });
            const removePromises = removals.concat(fatal).map((cid) => db.removeOp(cid));
            const failPromises = failed.map((r) => {
                const op = opsIndex[r.client_id];
                return db.bumpOpAttempt(r.client_id, (op && op.attempts ? op.attempts : 0) + 1);
            });

            return Promise.all(syncPromises.concat(removePromises, failPromises))
                .then(() => ({ failed: failed, fatal: fatal, removals: removals }));
        })
        .then((info) => {
            const errores = info.failed.length + info.fatal.length;
            const notificar = () => {
                if (errores) publicarEvento('app:sync-error', { count: errores });
                notifyChange();
            };
            if (info.removals.length && isOnline()) {
                return refreshSnapshots()
                    .then((snaps) => publicarEvento('app:data-refreshed', snaps || {}))
                    .catch(() => {})
                    .then(notificar);
            }
            notificar();
        });
}

export function refreshSnapshots() {
    if (!isOnline()) return Promise.resolve(null);
    const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    const fetchJson = (url) => fetch(url, {
        headers: headers,
        cache: 'no-store',
        redirect: 'manual',
    }).then((r) => {
        const tipo = r.headers.get('content-type') || '';
        return r.ok && tipo.indexOf('application/json') !== -1 ? r.json() : null;
    }).catch(() => null);

    return Promise.all([
        fetchJson('/api/offline/mesas'),
        fetchJson('/api/offline/menu'),
        fetchJson('/api/offline/pedidos'),
        fetchJson('/api/offline/session'),
    ]).then((resultados) => {
        const mesas = resultados[0];
        const menu = resultados[1];
        const pedidos = resultados[2];
        const session = resultados[3];
        const escrituras = [];
        if (mesas) escrituras.push(db.putSnapshot('mesas', mesas));
        if (menu) escrituras.push(db.putSnapshot('menu', menu));
        if (pedidos) escrituras.push(db.putSnapshot('pedidos', pedidos));
        if (session) escrituras.push(db.putSnapshot('session', session));
        return Promise.all(escrituras).then(() => ({
            mesas: mesas,
            menu: menu,
            pedidos: pedidos,
            session: session,
        }));
    });
}

export function precacheSnapshots() {
    return refreshSnapshots().catch(() => null);
}

export function loadSnapshot(key) {
    return db.getSnapshot(key).then((snap) => snap && snap.data ? snap.data : null);
}

export async function countPending() {
    return db.countPending();
}

function onReconnect() {
    syncNow()
        .catch(() => {})
        .then(() => precacheSnapshots())
        .then((snaps) => publicarEvento('app:data-refreshed', snaps || {}))
        .catch(() => {});
}

export function initConnectivityListeners() {
    initConnection();
    precacheSnapshots();
    onConnectionChange((evento) => {
        notifyChange();
        if (evento.detail && evento.detail.online) onReconnect();
    });
    // Reintento periódico para LAN sin Background Sync (o eventos de red perdidos).
    setInterval(() => {
        if (isOnline()) {
            db.getAllOps().then((ops) => {
                if (ops.length) syncNow().catch(() => {});
            }).catch(() => {});
        } else {
            probeOnline();
        }
    }, 30000);
}

export function borrarDatosOffline() {
    const CACHES_DATOS = ['app-pages', 'pos-pages', 'offline-page', 'offline-api'];
    return Promise.all([
        typeof caches !== 'undefined'
            ? caches.keys().then((claves) => Promise.all(
                claves.filter((c) => CACHES_DATOS.some((prefijo) => c.indexOf(prefijo) !== -1))
                    .map((c) => caches.delete(c))
            ))
            : Promise.resolve(),
        new Promise((resolve) => {
            const req = indexedDB.deleteDatabase('laravel-bar-offline');
            req.onsuccess = req.onerror = req.onblocked = () => resolve();
        }),
    ]).catch(() => {});
}
