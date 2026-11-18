// Refresco en tiempo real del listado /pedidos.
//
// Escucha el evento `pedido:cambio` (emitido desde notificaciones.js a partir
// del broadcast de Reverb) y sustituye la lista por el fragmento actualizado
// sin recargar la página. Mantiene GRUPOS_PG sincronizado para el modal Pagar.

import { isOnline } from './connection';

const DEBOUNCE_MS = 400;
// Respaldo por si el WebSocket no está disponible (proxy, red, etc.):
// refresca la lista aunque no llegue el evento, sin recargar la página.
const POLL_MS = 15000;

function csrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function init() {
    const cont = document.getElementById('pedidos-contenido');
    if (!cont) return;

    const url = cont.dataset.fragmentoUrl || '/pedidos/fragmento';
    let temporizador = null;
    let refrescando = false;
    let pendiente = false;

    function tabActual() {
        const params = new URLSearchParams(window.location.search);
        return params.get('tab') || cont.dataset.tab || 'activos';
    }

    function actualizarContador(tab, valor) {
        const link = cont.querySelector('.mesa-view-tab[href*="tab=' + tab + '"]');
        if (!link) return;

        const chip = link.querySelector('.nav-chip');
        const n = Number(valor) || 0;

        if (n > 0) {
            if (chip) {
                chip.textContent = String(n);
            } else {
                const span = document.createElement('span');
                span.className = 'nav-chip';
                span.textContent = String(n);
                link.appendChild(span);
            }
        } else if (chip) {
            chip.remove();
        }
    }

    function aplicar(datos) {
        if (typeof datos.html === 'string') cont.innerHTML = datos.html;
        if (Array.isArray(datos.grupos)) window.GRUPOS_PG = datos.grupos;
        actualizarContador('activos', datos.activosCount);
        actualizarContador('historial', datos.historialCount);
    }

    async function refrescar() {
        if (!isOnline()) return;
        if (refrescando) {
            pendiente = true;
            return;
        }

        refrescando = true;

        try {
            const res = await fetch(url + '?tab=' + encodeURIComponent(tabActual()), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf(),
                },
                credentials: 'same-origin',
            });

            if (res.ok) aplicar(await res.json());
        } catch (e) {
            // Sin conexión: el polling de la campana lo reintentará.
        } finally {
            refrescando = false;

            if (pendiente) {
                pendiente = false;
                refrescar();
            }
        }
    }

    window.addEventListener('pedido:cambio', () => {
        clearTimeout(temporizador);
        temporizador = setTimeout(refrescar, DEBOUNCE_MS);
    });

    let intervalo = null;

    function activarPolling() {
        if (intervalo) return;
        intervalo = setInterval(refrescar, POLL_MS);
    }

    function desactivarPolling() {
        clearInterval(intervalo);
        intervalo = null;
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            desactivarPolling();
        } else {
            refrescar();
            activarPolling();
        }
    });

    window.addEventListener('app:data-refreshed', refrescar);
    activarPolling();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
