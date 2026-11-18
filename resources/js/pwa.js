import { setCSRF, isOnline, syncNow, countPending, initConnectivityListeners, enqueuePedido, enqueuePedidoEstado, enqueuePagoMesa, enqueueCerrarMesa, loadSnapshot, precacheSnapshots, refreshSnapshots, probeOnline, borrarDatosOffline } from './offline';
import { onDataRefreshed } from './connection';

function csrfFromDom() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

let pendingCount = 0;
let online = typeof navigator !== 'undefined' ? navigator.onLine : true;
let deferredPrompt = null;
let installedStandalone = false;
let isStaff = false;
let dismissed = !!window.__pwaInstallDismissed;

function buildIndicator() {
    if (document.getElementById('offline-indicator')) return;
    const host = document.createElement('div');
    host.id = 'offline-indicator';
    host.innerHTML =
        '<style>' +
        '#offline-ui{position:fixed;bottom:18px;left:50%;transform:translateX(-50%);z-index:99999;' +
        'display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:99px;' +
        'font-family:"DM Sans",sans-serif;font-size:12.5px;font-weight:500;letter-spacing:.01em;' +
        'box-shadow:0 6px 24px rgba(0,0,0,.35);border:1px solid rgba(200,146,42,.35);' +
        'transition:all .25s ease;backdrop-filter:blur(4px);}' +
        '#offline-ui.offline{background:#1a1008;color:#f0c97a;}' +
        '#offline-ui.online{background:#0f2318;color:#7de0a4;border-color:rgba(76,175,125,.4);}' +
        '#offline-ui.online.hide{opacity:0;pointer-events:none;transform:translateX(-50%) translateY(12px);}' +
        '#offline-ui .dot{width:8px;height:8px;border-radius:50%;background:currentColor;box-shadow:0 0 8px currentColor;}' +
        '#offline-ui #offline-label{cursor:pointer;}' +
        '#offline-ui .sync-btn{background:rgba(200,146,42,.15);border:1px solid rgba(200,146,42,.5);color:#f0c97a;' +
        'font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px;cursor:pointer;font-family:inherit;}' +
        '#offline-ui .sync-btn:hover{background:rgba(200,146,42,.3);}' +
        '</style>' +
        '<div id="offline-ui" class="offline" style="opacity:0">' +
        '<span class="dot"></span>' +
        '<span id="offline-label"></span>' +
        '<button class="sync-btn" id="offline-sync-btn" style="display:none"><i class="fa-solid fa-rotate"></i> Sincronizar</button>' +
        '</div>';
    document.body.appendChild(host);
}

function update() {
    offlineUpdate(online, pendingCount);
}

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true
        || (typeof window.__pwaInstalled === 'boolean' && window.__pwaInstalled);
}

function buildInstallButton() {
    if (!isStaff) return;
    if (document.getElementById('pwa-install-bar')) return;
    const bar = document.createElement('div');
    bar.id = 'pwa-install-bar';
    bar.style.cssText =
        'position:fixed;bottom:16px;left:50%;transform:translateX(-50%);z-index:99998;display:none;' +
        'align-items:center;gap:10px;padding:8px 10px 8px 14px;border-radius:99px;' +
        'border:1px solid rgba(200,146,42,.5);background:rgba(24,17,8,.92);color:#f0c97a;' +
        'font-family:"DM Sans",sans-serif;font-size:12.5px;font-weight:500;letter-spacing:.01em;' +
        'box-shadow:0 6px 24px rgba(0,0,0,.4);backdrop-filter:blur(4px);' +
        'max-width:calc(100vw - 24px);transition:opacity .25s ease;';
    bar.innerHTML =
        '<i class="fa-solid fa-download" style="font-size:12px;flex-shrink:0"></i>' +
        '<span style="white-space:nowrap">Instala la app para acceso rápido</span>' +
        '<button id="pwa-install-btn" style="' +
        'background:#c8922a;color:#fff;border:none;padding:6px 14px;border-radius:99px;' +
        'font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;flex-shrink:0;transition:background .15s;' +
        '">Instalar</button>' +
        '<button id="pwa-install-close" aria-label="Cerrar" title="Cerrar" style="' +
        'width:24px;height:24px;border-radius:50%;border:none;background:rgba(255,255,255,.08);color:#f0c97a;' +
        'font-size:15px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;' +
        'flex-shrink:0;transition:background .15s;' +
        '">×</button>';

    const btn = bar.querySelector('#pwa-install-btn');
    const close = bar.querySelector('#pwa-install-close');
    btn.onmouseenter = () => { btn.style.background = '#dba34e'; };
    btn.onmouseleave = () => { btn.style.background = '#c8922a'; };
    close.onmouseenter = () => { close.style.background = 'rgba(255,255,255,.2)'; };
    close.onmouseleave = () => { close.style.background = 'rgba(255,255,255,.08)'; };
    btn.onclick = () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
    };
    close.onclick = () => {
        dismissed = true;
        bar.style.display = 'none';
        fetch('/pwa/install-dismissed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfFromDom(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).catch(() => {});
    };
    document.body.appendChild(bar);
}

function refreshInstallButton() {
    const bar = document.getElementById('pwa-install-bar');
    if (!bar) return;
    if (deferredPrompt && !installedStandalone && !isStandalone() && isStaff && !dismissed) {
        bar.style.display = 'inline-flex';
    } else {
        bar.style.display = 'none';
    }
}

export function offlineUpdate(networkOnline, pending) {
    online = networkOnline;
    pendingCount = pending;
    buildIndicator();
    const ui = document.getElementById('offline-ui');
    if (!ui) return;
    const label = document.getElementById('offline-label');
    const btn = document.getElementById('offline-sync-btn');
    if (!online) {
        ui.classList.add('offline');
        ui.classList.remove('online', 'hide');
        label.textContent = pending
            ? `Sin conexión · ${pending} pedido(s) en espera`
            : 'Sin conexión';
        btn.style.display = pending ? 'inline-flex' : 'none';
    } else if (pending) {
        ui.classList.add('online');
        ui.classList.remove('offline', 'hide');
        label.textContent = `${pending} pedido(s) pendiente(s) de sincronizar`;
        btn.style.display = 'inline-flex';
    } else {
        ui.classList.add('online', 'hide');
        ui.classList.remove('offline');
        label.textContent = 'Conectado';
        btn.style.display = 'none';
    }
}

function mostrarAvisoActualizacion() {
    if (document.getElementById('pwa-update-aviso')) return;
    const aviso = document.createElement('div');
    aviso.id = 'pwa-update-aviso';
    aviso.style.cssText =
        'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100001;' +
        'display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:99px;' +
        'background:#0f2318;color:#7de0a4;border:1px solid rgba(76,175,125,.4);' +
        'font-family:"DM Sans",sans-serif;font-size:12.5px;box-shadow:0 6px 24px rgba(0,0,0,.35);';
    aviso.innerHTML = '<span>Datos actualizados · recarga para verlos</span>' +
        '<button id="pwa-update-btn" style="background:rgba(125,224,164,.16);border:1px solid rgba(125,224,164,.5);' +
        'color:#7de0a4;padding:4px 10px;border-radius:99px;cursor:pointer;font:inherit;font-weight:600">Recargar</button>';
    document.body.appendChild(aviso);
    aviso.querySelector('#pwa-update-btn').onclick = () => window.location.reload();
    setTimeout(() => aviso.remove(), 15000);
}

function recargarSiEsPosible() {
    if (window.__pwaEsPos) return;
    if (window.__pwaFormularioSucio) {
        mostrarAvisoActualizacion();
        return;
    }
    window.__pwaRecargando = true;
    window.location.reload();
}

function initLimpiezaLogout() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (!form.action || form.action.indexOf('/logout') === -1) return;
        if (form.dataset.pwaLogout) return;
        e.preventDefault();
        form.dataset.pwaLogout = '1';
        borrarDatosOffline().then(() => form.submit());
    }, true);
}

export function initPWA() {
    isStaff = window.__isStaff === true;
    setCSRF(csrfFromDom());
    initConnectivityListeners();
    buildInstallButton();

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        refreshInstallButton();
    });
    window.addEventListener('appinstalled', () => {
        installedStandalone = true;
        deferredPrompt = null;
        refreshInstallButton();
    });
    const mql = window.matchMedia('(display-mode: standalone)');
    if (mql.addEventListener) mql.addEventListener('change', refreshInstallButton);
    refreshInstallButton();

    const aplicarPendientes = (p) => offlineUpdate(isOnline(), typeof p === 'number' ? p : 0);
    if (window.__offlineOnChange === null || window.__offlineOnChange === undefined) {
        window.__offlineOnChange = () => {
            countPending().then(aplicarPendientes).catch(() => aplicarPendientes(0));
        };
    }

    countPending().then(aplicarPendientes).catch(() => aplicarPendientes(0));

    const refresh = () => {
        countPending().then(aplicarPendientes).catch(() => {});
    };
    window.addEventListener('app:online', refresh);
    window.addEventListener('app:offline', refresh);
    setInterval(refresh, 10000);

    onDataRefreshed(recargarSiEsPosible);
    window.addEventListener('app:sesion-expirada', () => {
        buildIndicator();
        const label = document.getElementById('offline-label');
        if (label) label.textContent = 'Sesión expirada · inicia sesión para sincronizar';
    });

    document.addEventListener('input', () => { window.__pwaFormularioSucio = true; }, true);
    document.addEventListener('change', () => { window.__pwaFormularioSucio = true; }, true);
    document.addEventListener('submit', () => { window.__pwaFormularioSucio = false; }, true);
    initLimpiezaLogout();

    buildIndicator();
    document.addEventListener('click', (e) => {
        if (!e.target.closest) return;
        if (e.target.closest('#offline-sync-btn')) {
            const btn = document.getElementById('offline-sync-btn');
            if (btn) btn.disabled = true;
            syncNow(true)
                .finally(() => {
                    refresh();
                    if (btn) btn.disabled = false;
                });
            return;
        }
        if (!isOnline() && e.target.closest('#offline-label') && window.location.pathname !== '/offline') {
            window.location.href = '/offline';
        }
    });

    // Expose for blade (POS) usage
    window.OfflineApp = {
        online: online,
        isOnline,
        syncNow,
        countPending,
        enqueuePedido,
        enqueuePedidoEstado,
        enqueuePagoMesa,
        enqueueCerrarMesa,
        loadSnapshot,
        precacheSnapshots,
        refreshSnapshots,
        probeOnline,
        borrarDatosOffline,
        getCSRF: () => csrfFromDom(),
    };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPWA);
} else {
    initPWA();
}
