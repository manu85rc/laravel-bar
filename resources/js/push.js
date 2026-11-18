// Suscripción a Web Push (notificaciones del sistema operativo).
//
// - Sólo tiene sentido para el personal (window.__isStaff).
// - Requiere contexto seguro (HTTPS o localhost): en LAN http no hay push,
//   ahí la campana + el tiempo real siguen siendo el aviso en vivo.
// - iOS 16.4+: sólo funciona si la PWA está instalada en el inicio y el
//   permiso se pide dentro de un gesto del usuario (clic en la campana).

const URL_SUBSCRIBIR = '/push/subscribe';
const URL_DESUSCRIBIR = '/push/unsubscribe';

function esStaff() {
    return window.__isStaff === true;
}

function csrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function cabeceras() {
    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest',
    };
}

function soportaPush() {
    return (
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'showNotification' in ServiceWorkerRegistration.prototype
    );
}

function clavePublica() {
    const meta = document.querySelector('meta[name="vapid-public-key"]');
    return meta ? meta.content : '';
}

// La clave VAPID viene en base64url (estándar de Web Push).
function base64urlAUint8(valor) {
    const padding = '='.repeat((4 - (valor.length % 4)) % 4);
    const base64 = (valor + padding).replace(/-/g, '+').replace(/_/g, '/');

    const binario = window.atob(base64);
    const bytes = new Uint8Array(binario.length);

    for (let i = 0; i < binario.length; i++) {
        bytes[i] = binario.charCodeAt(i);
    }

    return bytes;
}

function datosDeSuscripcion(suscripcion) {
    const json = suscripcion.toJSON();

    return {
        endpoint: json.endpoint,
        p256dh: json.keys.p256dh,
        auth: json.keys.auth,
        content_encoding: 'aes128gcm',
    };
}

async function registrarEnServidor(suscripcion) {
    const res = await fetch(URL_SUBSCRIBIR, {
        method: 'POST',
        headers: cabeceras(),
        credentials: 'same-origin',
        body: JSON.stringify(datosDeSuscripcion(suscripcion)),
    });

    return res.ok;
}

async function darDeBajaEnServidor(suscripcion) {
    const res = await fetch(URL_DESUSCRIBIR, {
        method: 'DELETE',
        headers: cabeceras(),
        credentials: 'same-origin',
        body: JSON.stringify({ endpoint: suscripcion ? suscripcion.endpoint : null }),
    });

    return res.ok;
}

/**
 * Pide permiso y deja la suscripción activa. Debe llamarse desde un gesto
 * del usuario (clic de la campana). Devuelve true si quedó suscrito.
 */
async function activarPush() {
    if (!soportaPush() || !clavePublica()) return false;

    try {
        const permiso = await Notification.requestPermission();
        if (permiso !== 'granted') return false;

        const registro = await navigator.serviceWorker.ready;
        let suscripcion = await registro.pushManager.getSubscription();

        if (!suscripcion) {
            suscripcion = await registro.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: base64urlAUint8(clavePublica()),
            });
        }

        await registrarEnServidor(suscripcion);

        return true;
    } catch (e) {
        console.warn('[push] no fue posible suscribirse', e);
        return false;
    }
}

/**
 * Cancela el push (permiso + servidor + suscripción del navegador).
 */
async function desactivarPush() {
    if (!('serviceWorker' in navigator)) return;

    try {
        const registro = await navigator.serviceWorker.ready;
        const suscripcion = await registro.pushManager.getSubscription();

        await darDeBajaEnServidor(suscripcion);
        if (suscripcion) await suscripcion.unsubscribe();
    } catch (e) {
        console.warn('[push] no fue posible darse de baja', e);
    }
}

/**
 * Estado actual para decidir si usar la notificación de escritorio como
 * respaldo dentro de la página abierta.
 */
async function estadoPush() {
    if (!soportaPush() || !clavePublica()) return 'no-disponible';
    if (Notification.permission === 'denied') return 'bloqueado';
    if (Notification.permission !== 'granted') return 'inactivo';

    try {
        const registro = await navigator.serviceWorker.ready;
        return (await registro.pushManager.getSubscription()) ? 'activo' : 'inactivo';
    } catch (e) {
        return 'inactivo';
    }
}

/**
 * Al volver a la app re-registra la suscripción si el navegador la renovó
 * (p. ej. tras un `pushsubscriptionchange` mientras estaba cerrada).
 */
async function sincronizarSuscripcion() {
    if (!soportaPush() || !clavePublica() || Notification.permission !== 'granted') return;

    try {
        const registro = await navigator.serviceWorker.ready;
        const suscripcion = await registro.pushManager.getSubscription();

        if (suscripcion) await registrarEnServidor(suscripcion);
    } catch (e) {
        // Silencioso: se reintentará en la próxima carga.
    }
}

// El service worker avisa cuando el navegador renovó la suscripción push.
navigator.serviceWorker?.addEventListener('message', (evento) => {
    if (evento.data && evento.data.tipo === 'push-suscripcion-cambio') {
        sincronizarSuscripcion();
    }
});

if (esStaff()) {
    sincronizarSuscripcion();
}

export { activarPush, desactivarPush, estadoPush, soportaPush };