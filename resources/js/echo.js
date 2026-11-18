import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Por defecto se usa el MISMO origen que sirve la app: el WebSocket viaja por
// `/app` y el proxy (nginx del contenedor y el proxy TLS delante) lo reenvía a
// Reverb. Así funciona igual en LAN (http) y en producción (https, sin mixed
// content). Sólo si `VITE_REVERB_HOST` apunta a otro host se usa ese host.
const envHost = import.meta.env.VITE_REVERB_HOST;
const envPort = Number(import.meta.env.VITE_REVERB_PORT || 0);
const envScheme = import.meta.env.VITE_REVERB_SCHEME;
const tieneHostExterno = !!envHost && envHost !== 'localhost';

const secure = tieneHostExterno ? envScheme === 'https' : window.location.protocol === 'https:';
const host = tieneHostExterno ? envHost : window.location.hostname;
// Con host externo se respeta VITE_REVERB_PORT; en mismo origen se usa el
// puerto de la página (8000 en LAN, 443 en HTTPS).
const port = tieneHostExterno
    ? (envPort || (secure ? 443 : 8080))
    : (Number(window.location.port) || (secure ? 443 : 80));

const csrf = document.querySelector('meta[name="csrf-token"]');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: secure,
    enabledTransports: secure ? ['wss'] : ['ws'],
    // Autoriza los canales privados (`/broadcasting/auth`) con la sesión web.
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrf ? csrf.content : '',
        },
    },
});
