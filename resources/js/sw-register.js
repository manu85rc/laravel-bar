import { registerSW } from 'virtual:pwa-register';

const updateSW = registerSW({
    immediate: true,
    onOfflineReady() {
        if (window.__pwaOfflineReady) window.__pwaOfflineReady();
    },
});

if ('serviceWorker' in navigator) {
    const yaControlada = !!navigator.serviceWorker.controller;
    let recargando = false;
    let offlineCacheado = false;

    function calentarOffline() {
        if (offlineCacheado) return;
        offlineCacheado = true;
        fetch('/offline').catch(() => {});
    }

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        calentarOffline();
        if (!yaControlada || recargando || window.__pwaRecargando) return;
        recargando = true;
        window.__pwaRecargando = true;
        window.location.reload();
    });

    navigator.serviceWorker.ready.then(calentarOffline).catch(() => {});
}

export { updateSW };
