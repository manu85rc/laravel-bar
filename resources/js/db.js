const DB_NAME = 'laravel-bar-offline';
const DB_VERSION = 1;

let _dbPromise = null;

function openDB() {
    if (_dbPromise) return _dbPromise;
    _dbPromise = new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pending_ops')) {
                const store = db.createObjectStore('pending_ops', { keyPath: 'client_id' });
                store.createIndex('created_at', 'created_at');
            }
            if (!db.objectStoreNames.contains('snapshots')) {
                db.createObjectStore('snapshots', { keyPath: 'key' });
            }
            if (!db.objectStoreNames.contains('synced_pedidos')) {
                db.createObjectStore('synced_pedidos', { keyPath: 'client_id' });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
    return _dbPromise;
}

function tx(storeName, mode, fn) {
    return openDB().then((db) => {
        return new Promise((resolve, reject) => {
            const t = db.transaction(storeName, mode);
            const store = t.objectStore(storeName);
            let result;
            try {
                result = fn(store);
            } catch (err) {
                reject(err);
                return;
            }
            t.oncomplete = () => resolve(result && result.result !== undefined ? result.result : undefined);
            t.onerror = () => reject(t.error);
            t.onabort = () => reject(t.error);
        });
    });
}

function makeClientId() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return 'cid-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
}

export function putOp(op) {
    if (!op.client_id) op.client_id = makeClientId();
    op.created_at = Date.now();
    if (typeof op.attempts !== 'number') op.attempts = 0;
    return tx('pending_ops', 'readwrite', (s) => s.put(op));
}

export function getAllOps() {
    return tx('pending_ops', 'readonly', (s) => s.getAll());
}

export function removeOp(clientId) {
    return tx('pending_ops', 'readwrite', (s) => s.delete(clientId));
}

export function bumpOpAttempt(clientId, attempts) {
    return tx('pending_ops', 'readwrite', (s) => {
        const req = s.get(clientId);
        req.onsuccess = () => {
            if (req.result) s.put({ ...req.result, attempts });
        };
    });
}

export function putSnapshot(key, data, ts) {
    return tx('snapshots', 'readwrite', (s) => s.put({ key, data, ts: ts || Date.now() }));
}

export function getSnapshot(key) {
    return tx('snapshots', 'readonly', (s) => s.get(key));
}

export function getAllSnapshots() {
    return tx('snapshots', 'readonly', (s) => s.getAll());
}

export function putSyncedPedido(clientId, serverId) {
    return tx('synced_pedidos', 'readwrite', (s) => s.put({ client_id: clientId, server_id: serverId, synced_at: Date.now() }));
}

export function countPending() {
    return getAllOps().then((ops) => ops.length);
}

export default {
    putOp,
    getAllOps,
    removeOp,
    bumpOpAttempt,
    putSnapshot,
    getSnapshot,
    getAllSnapshots,
    putSyncedPedido,
    countPending,
};
