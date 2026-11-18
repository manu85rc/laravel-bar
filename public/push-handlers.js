// Manejadores de Web Push del service worker.
//
// Este script se inyecta en el sw.js generado por vite-plugin-pwa mediante
// `workbox.importScripts: ['/push-handlers.js']`. Se registra a nivel de
// WorkerGlobalScope (clásico) y funciona aunque la app esté cerrada.

const CASUAL_BAR_TAG = 'casual-bar';

self.addEventListener('push', (event) => {
    let datos = { title: 'Casual Bar', body: 'Nueva notificación' };

    if (event.data) {
        try {
            datos = Object.assign(datos, event.data.json());
        } catch (e) {
            // Si el payload no es JSON (p. ej. push de sistema), se muestra el por defecto.
        }
    }

    const titulo = datos.title || 'Casual Bar';
    const opciones = {
        body: datos.body || '',
        icon: datos.icon || '/pwa-192x192.png',
        badge: datos.badge || '/pwa-192x192.png',
        tag: datos.tag || CASUAL_BAR_TAG,
        data: { url: datos.url || '/pedidos' },
    };

    event.waitUntil(self.registration.showNotification(titulo, opciones));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = (event.notification.data && event.notification.data.url) || '/pedidos';
    const abrirUrl = new URL(url, self.location.origin).toString();

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientes) => {
            for (const cliente of clientes) {
                if ('focus' in cliente) {
                    cliente.navigate(abrirUrl);
                    return cliente.focus();
                }
            }

            return self.clients.openWindow(abrirUrl);
        })
    );
});

// Si el navegador renueva la suscripción push (endpoint/p256dh/auth nuevos),
// pedimos a alguna pestaña abierta que la vuelva a registrar en el servidor.
self.addEventListener('pushsubscriptionchange', (event) => {
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientes) => {
            if (!clientes.length) {
                return;
            }

            clientes[0].postMessage({ tipo: 'push-suscripcion-cambio' });
        })
    );
});