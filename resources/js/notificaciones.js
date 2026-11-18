// Campana de notificaciones ("pedido listo").
//
// - Si encuentra #notifWrap (header del admin) usa esa campana.
// - Si no existe (página del POS, que no carga el header), crea una
//   campana flotante para que el personal también vea los avisos.
//
// Sólo se activa para personal (window.__isStaff), que es quien recibe
// las notificaciones, y nunca rompe la página si el backend falla u
// se está sin conexión: en ese caso simplemente reintenta en el próximo poll.

import { activarPush, estadoPush } from './push';

const URL_INDEX = '/notificaciones';
const URL_LEER_TODAS = '/notificaciones/leer-todas';
const POLL_MS = 30000;
const LIMITE = 15;

// Cuando el push del SO está activo, las notificaciones de escritorio dentro
// de la página se desactivan para no mostrar el aviso dos veces.
let pushActivo = false;

let noLeidas = 0;
let items = [];
let panelAbierto = false;
let wrap = null;
let panel = null;
let campana = null;
let badge = null;

// Audio: se crea/desbloquea perezosamente en el primer gesto del usuario
// (los navegadores bloquean el autoplay hasta que hay interacción).
let audioCtx = null;

// Pestaña: guardamos el título y el favicon originales para poder restaurarlos
// cuando el contador de no leídas vuelva a cero.
let tituloOriginal = null;
let faviconLink = null;
let faviconOriginal = null;
let faviconImg = null;

function csrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function cabeceras(conCuerpo) {
    const h = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    if (conCuerpo) h['Content-Type'] = 'application/json';
    h['X-CSRF-TOKEN'] = csrf();
    return h;
}

function estoyOnline() {
    return typeof navigator === 'undefined' || navigator.onLine !== false;
}

function esPersonal() {
    return window.__isStaff === true;
}

/* ------------------------------------------------------------------ */
/* Sonido (Web Audio API)                                              */
/* ------------------------------------------------------------------ */
/**
 * Crea/reanuda el AudioContext. Debe llamarse desde un gesto del usuario
 * (click, tecla o toque): hasta que no haya interacción el navegador lo
 * mantiene en estado 'suspended' y no suena nada.
 */
function desbloquearAudio() {
    try {
        if (!audioCtx) {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            audioCtx = new Ctx();
        }
        if (audioCtx.state === 'suspended') audioCtx.resume();
    } catch (e) {
        // Navegador sin Web Audio: se ignora.
    }
}

/**
 * Aviso sonoro de "pedido nuevo/actualizado": dos tonos cortos con rampa de
 * ganancia (para no chasquear). En móvil añade una vibración corta.
 */
function sonarAlerta() {
    if (!audioCtx) desbloquearAudio();

    try {
        if (audioCtx) {
            if (audioCtx.state === 'suspended') audioCtx.resume();

            const inicio = audioCtx.currentTime;
            const tonos = [880, 1320];

            tonos.forEach((frecuencia, i) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                const t = inicio + i * 0.18;

                osc.type = 'sine';
                osc.frequency.value = frecuencia;

                gain.gain.setValueAtTime(0.0001, t);
                gain.gain.exponentialRampToValueAtTime(0.25, t + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.16);

                osc.connect(gain).connect(audioCtx.destination);
                osc.start(t);
                osc.stop(t + 0.18);
            });
        }

        if (navigator.vibrate) navigator.vibrate([150, 80, 150]);
    } catch (e) {
        // Silencioso: nunca debe romper la campana.
    }
}

/* ------------------------------------------------------------------ */
/* Pestaña del navegador: título (N) + badge en el favicon             */
/* ------------------------------------------------------------------ */
function capturarPestana() {
    tituloOriginal = document.title;
    faviconLink = document.querySelector('link[rel="icon"]');

    if (!faviconLink) return;

    faviconOriginal = faviconLink.href;

    if (!faviconImg) {
        faviconImg = new Image();
        faviconImg.crossOrigin = 'anonymous';
        faviconImg.onload = () => {
            if (noLeidas > 0) dibujarBadgeFavicon(noLeidas);
        };
        faviconImg.src = faviconOriginal;
    }
}

function restaurarFavicon() {
    if (faviconLink && faviconOriginal) faviconLink.href = faviconOriginal;
}

function dibujarBadgeFavicon(total) {
    if (!faviconLink || !faviconImg || !faviconImg.complete || !faviconImg.naturalWidth) {
        return;
    }

    const lado = 64;
    const canvas = document.createElement('canvas');
    canvas.width = lado;
    canvas.height = lado;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    ctx.clearRect(0, 0, lado, lado);
    ctx.drawImage(faviconImg, 0, 0, lado, lado);

    const etiqueta = total > 99 ? '99+' : String(total);
    const radio = total > 9 ? 24 : 22;
    const cx = lado - radio;
    const cy = lado - radio;

    ctx.beginPath();
    ctx.arc(cx, cy, radio, 0, Math.PI * 2);
    ctx.fillStyle = '#c0392b';
    ctx.fill();
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#ffffff';
    ctx.stroke();

    ctx.fillStyle = '#ffffff';
    ctx.font = `bold ${total > 9 ? 24 : 28}px sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(etiqueta, cx, cy + 1);

    try {
        faviconLink.href = canvas.toDataURL('image/png');
    } catch (e) {
        // Favicon de otro origen sin CORS: se deja el original.
    }
}

function actualizarPestana() {
    if (tituloOriginal === null) capturarPestana();

    if (tituloOriginal !== null) {
        document.title = noLeidas > 0
            ? `(${noLeidas}) ${tituloOriginal}`
            : tituloOriginal;
    }

    if (noLeidas > 0) {
        dibujarBadgeFavicon(noLeidas);
    } else {
        restaurarFavicon();
    }
}

/* ------------------------------------------------------------------ */
/* Tiempo real (Reverb/Echo) + notificación de escritorio              */
/* ------------------------------------------------------------------ */
function mensajePedido(payload) {
    const mesa = payload.mesa || 'Pedido';

    if (payload.accion === 'creado') {
        return `${mesa}: se creó el pedido #${payload.pedido_id}.`;
    }

    const estado = payload.estado
        ? payload.estado.charAt(0).toUpperCase() + payload.estado.slice(1)
        : 'actualizado';

    return `${mesa}: el pedido #${payload.pedido_id} pasó a ${estado}.`;
}

async function pedirPermisoPush() {
    const activado = await activarPush().catch(() => false);
    if (activado) pushActivo = true;
}

/**
 * Aviso de escritorio mientras la página está abierta. Sólo actúa como
 * respaldo cuando el push del sistema no está disponible/activo (p. ej.
 * en redes LAN sin HTTPS), para no duplicar notificaciones.
 */
function notificarEscritorio(payload) {
    if (pushActivo) return;
    if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;

    try {
        const icono = document.querySelector('link[rel="icon"]');
        const aviso = new Notification(
            payload.accion === 'creado' ? 'Nuevo pedido' : 'Pedido actualizado',
            {
                body: mensajePedido(payload),
                tag: `pedido-${payload.pedido_id || ''}`,
                icon: icono ? icono.href : undefined,
                silent: true,
            }
        );

        aviso.onclick = function () {
            window.focus();
            aviso.close();
        };
    } catch (e) {
        // Algunos navegadores no permiten constructor directo: se ignora.
    }
}

function suscribirEcho() {
    if (!window.Echo || typeof window.Echo.private !== 'function') return;

    window.Echo.private('staff').listen('.pedido.cambio', (payload) => {
        refrescar(false);
        window.dispatchEvent(new CustomEvent('pedido:cambio', { detail: payload }));
        notificarEscritorio(payload || {});
    });

    // Diagnóstico: deja rastro en consola del estado del WebSocket. Si el
    // tiempo real no conecta, el sondeo de respaldo mantiene la app al día.
    const conexion = window.Echo.connector && window.Echo.connector.pusher
        ? window.Echo.connector.pusher.connection
        : null;

    if (conexion && typeof conexion.bind === 'function') {
        conexion.bind('connected', () => console.info('[pedidos] tiempo real conectado'));
        conexion.bind('disconnected', () => console.warn('[pedidos] tiempo real desconectado'));
        conexion.bind('failed', () => console.error('[pedidos] tiempo real no disponible'));
        conexion.bind('error', (e) => console.error('[pedidos] error de tiempo real', e));
    }
}

/* ------------------------------------------------------------------ */
/* Estilos (se inyectan una vez para no depender del layout)           */
/* ------------------------------------------------------------------ */
function inyectarEstilos() {
    if (document.getElementById('notif-estilos')) return;

    const style = document.createElement('style');
    style.id = 'notif-estilos';
    style.textContent = [
        '.notif-wrap{position:relative;}',
        '.notif-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border-dim,rgba(0,0,0,.08));',
        'background:var(--bg-3,#fff);color:var(--text-3,#8a7d68);font-size:14px;cursor:pointer;',
        'display:flex;align-items:center;justify-content:center;position:relative;transition:all .15s;font-family:inherit;}',
        '.notif-btn:hover{border-color:var(--border,rgba(200,146,42,.2));color:var(--text-2,#5a4e3c);}',
        '.notif-btn.ping{animation:notif-ping 1s ease 2;}',
        '@keyframes notif-ping{0%{transform:scale(1);}35%{transform:scale(1.18);}100%{transform:scale(1);}}',
        '#notifBadge[hidden]{display:none!important;}',
        '#notifBadge{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;padding:0 4px;',
        'border-radius:99px;background:var(--red,#c0392b);color:#fff;font-size:9.5px;font-weight:700;',
        'line-height:17px;text-align:center;box-shadow:0 0 0 1.5px var(--bg-2,#f0ece4);}',
        '.notif-panel{position:absolute;top:calc(100% + 10px);right:0;width:330px;max-width:calc(100vw - 24px);',
        'background:var(--bg-3,#fff);border:1px solid var(--border,rgba(200,146,42,.2));border-radius:14px;',
        'box-shadow:0 14px 42px rgba(0,0,0,.3);z-index:130;overflow:hidden;display:none;text-align:left;}',
        '.notif-panel.open{display:block;}',
        '.notif-panel-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:12px 14px;',
        'border-bottom:1px solid var(--border-dim,rgba(0,0,0,.08));}',
        '.notif-panel-title{font-size:13px;font-weight:600;color:var(--text-1,#1a1816);}',
        '.notif-panel-count{font-size:10.5px;font-weight:700;color:var(--gold,#c8922a);background:var(--gold-glow,rgba(200,146,42,.12));',
        'padding:2px 8px;border-radius:99px;}',
        '.notif-list{max-height:320px;overflow-y:auto;}',
        '.notif-item{display:flex;gap:10px;padding:11px 14px;border-bottom:1px solid var(--border-dim,rgba(0,0,0,.08));',
        'cursor:pointer;transition:background .12s;}',
        '.notif-item:last-child{border-bottom:none;}',
        '.notif-item:hover{background:var(--bg-4,#e8e2d8);}',
        '.notif-item.unread{background:var(--gold-subtle,rgba(200,146,42,.06));}',
        '.notif-icon{width:30px;height:30px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;',
        'justify-content:center;font-size:12px;background:var(--gold-glow,rgba(200,146,42,.12));color:var(--gold,#c8922a);}',
        '.notif-icon.verde{background:var(--green-bg,rgba(45,143,94,.08));color:var(--green,#2d8f5e);}',
        '.notif-body{flex:1;min-width:0;}',
        '.notif-title{font-size:12.5px;font-weight:500;color:var(--text-2,#5a4e3c);}',
        '.notif-item.unread .notif-title{font-weight:700;color:var(--text-1,#1a1816);}',
        '.notif-msg{font-size:11.5px;color:var(--text-3,#8a7d68);margin-top:1px;line-height:1.35;}',
        '.notif-meta{font-size:10px;color:var(--text-4,#b0a594);margin-top:3px;}',
        '.notif-empty{padding:24px 14px;text-align:center;font-size:12.5px;color:var(--text-3,#8a7d68);}',
        '.notif-foot{display:flex;gap:8px;padding:10px 14px;border-top:1px solid var(--border-dim,rgba(0,0,0,.08));',
        'background:var(--bg-2,#f0ece4);}',
        '.notif-foot button{flex:1;padding:7px 10px;border-radius:8px;border:1px solid var(--border-dim,rgba(0,0,0,.08));',
        'background:var(--bg-3,#fff);color:var(--text-2,#5a4e3c);font-size:11.5px;font-weight:500;cursor:pointer;font-family:inherit;',
        'transition:all .12s;}',
        '.notif-foot button:hover{border-color:var(--border,rgba(200,146,42,.2));color:var(--text-1,#1a1816);}',
        '.notif-foot button:disabled{opacity:.5;cursor:default;}',
    ].join('\n');

    document.head.appendChild(style);
}

/* ------------------------------------------------------------------ */
/* Montaje                                                              */
/* ------------------------------------------------------------------ */
function crearCampanaFlotante() {
    const contenedor = document.createElement('div');
    contenedor.id = 'notifWrap';
    contenedor.className = 'notif-wrap';
    contenedor.style.cssText = 'position:fixed;top:14px;right:14px;z-index:130;';

    const boton = document.createElement('button');
    boton.type = 'button';
    boton.id = 'notifBell';
    boton.className = 'notif-btn';
    boton.setAttribute('aria-label', 'Notificaciones');
    boton.setAttribute('title', 'Notificaciones');
    boton.setAttribute('aria-haspopup', 'true');
    boton.setAttribute('aria-expanded', 'false');

    boton.innerHTML = '<i class="fa-solid fa-bell"></i><span id="notifBadge" hidden>0</span>';

    contenedor.appendChild(boton);
    document.body.appendChild(contenedor);

    return contenedor;
}

function crearPanel() {
    const caja = document.createElement('div');
    caja.className = 'notif-panel';
    caja.id = 'notifPanel';
    caja.innerHTML = [
        '<div class="notif-panel-head">',
        '<span class="notif-panel-title">Notificaciones</span>',
        '<span class="notif-panel-count" id="notifPanelCount">0 sin leer</span>',
        '</div>',
        '<div class="notif-list" id="notifList"></div>',
        '<div class="notif-foot">',
        '<button type="button" id="notifLeerTodas">Marcar todas como leídas</button>',
        '<button type="button" id="notifLimpiar">Limpiar</button>',
        '</div>',
    ].join('');

    return caja;
}

function montar() {
    if (wrap) return;

    inyectarEstilos();

    wrap = document.getElementById('notifWrap') || crearCampanaFlotante();
    campana = document.getElementById('notifBell');

    if (!campana) return;

    if (!campana.querySelector('#notifBadge')) {
        const nuevo = document.createElement('span');
        nuevo.id = 'notifBadge';
        nuevo.hidden = true;
        nuevo.textContent = '0';
        campana.appendChild(nuevo);
    }

    badge = campana.querySelector('#notifBadge');
    panel = crearPanel();
    wrap.appendChild(panel);

    campana.addEventListener('click', (e) => {
        e.stopPropagation();
        pedirPermisoPush();
        alternarPanel();
    });

    panel.addEventListener('click', (e) => {
        const item = e.target.closest('.notif-item');
        if (item) {
            abrirNotificacion(item.dataset.id);
            return;
        }
        if (e.target.closest('#notifLeerTodas')) leerTodas();
        if (e.target.closest('#notifLimpiar')) limpiar();
    });

    document.addEventListener('click', (e) => {
        if (!panelAbierto) return;
        if (e.target.closest && e.target.closest('#notifWrap')) return;
        cerrarPanel();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') cerrarPanel();
    });

    window.addEventListener('app:data-refreshed', () => refrescar(true));
}

/* ------------------------------------------------------------------ */
/* Render                                                               */
/* ------------------------------------------------------------------ */
function pintarCampana(ping) {
    actualizarPestana();

    if (!badge) return;

    badge.textContent = noLeidas > 99 ? '99+' : String(noLeidas);
    badge.hidden = noLeidas <= 0;

    if (campana) {
        campana.setAttribute('title', noLeidas > 0
            ? `Notificaciones (${noLeidas} sin leer)`
            : 'Notificaciones');
    }

    if (ping && noLeidas > 0 && campana) {
        campana.classList.remove('ping');
        // Forzar reflow para poder repetir la animación.
        void campana.offsetWidth;
        campana.classList.add('ping');
    }
}

function tiempoRelativo(iso) {
    if (!iso) return '';

    const fecha = new Date(iso);
    if (isNaN(fecha.getTime())) return '';

    const segundos = Math.max(0, Math.floor((Date.now() - fecha.getTime()) / 1000));

    if (segundos < 60) return 'hace un momento';
    if (segundos < 3600) {
        const min = Math.floor(segundos / 60);
        return `hace ${min} min`;
    }
    if (segundos < 86400) {
        const horas = Math.floor(segundos / 3600);
        return `hace ${horas} h`;
    }
    if (segundos < 2592000) {
        const dias = Math.floor(segundos / 86400);
        return dias === 1 ? 'ayer' : `hace ${dias} días`;
    }

    return fecha.toLocaleDateString('es', { day: '2-digit', month: '2-digit', year: '2-digit' });
}

/**
 * El icono viene del payload de la notificación: sólo se aceptan
 * clases de FontAwesome para no inyectar markup en el panel.
 */
function claseIcono(icono) {
    return typeof icono === 'string' && /^fa-[a-z0-9 -]+$/i.test(icono.trim())
        ? icono.trim()
        : 'fa-solid fa-bell';
}

function pintarPanel() {
    if (!panel) return;

    const lista = panel.querySelector('#notifList');
    const contador = panel.querySelector('#notifPanelCount');
    if (!lista) return;

    if (contador) {
        contador.textContent = noLeidas === 1 ? '1 sin leer' : `${noLeidas} sin leer`;
    }

    lista.innerHTML = '';

    if (!items.length) {
        const vacio = document.createElement('div');
        vacio.className = 'notif-empty';
        vacio.textContent = 'No tienes notificaciones.';
        lista.appendChild(vacio);
        return;
    }

    items.forEach((item) => {
        const fila = document.createElement('div');
        fila.className = 'notif-item' + (item.leida ? '' : ' unread');
        fila.dataset.id = item.id;

        const icono = document.createElement('span');
        icono.className = 'notif-icon' + (item.color === 'verde' ? ' verde' : '');
        icono.innerHTML = `<i class="${claseIcono(item.icono)}"></i>`;

        const cuerpo = document.createElement('div');
        cuerpo.className = 'notif-body';

        const titulo = document.createElement('div');
        titulo.className = 'notif-title';
        titulo.textContent = item.titulo || 'Notificación';

        const mensaje = document.createElement('div');
        mensaje.className = 'notif-msg';
        mensaje.textContent = item.mensaje || '';

        const meta = document.createElement('div');
        meta.className = 'notif-meta';
        meta.textContent = [item.detalle, tiempoRelativo(item.created_at)]
            .filter(Boolean)
            .join(' · ');

        cuerpo.appendChild(titulo);
        cuerpo.appendChild(mensaje);
        cuerpo.appendChild(meta);

        fila.appendChild(icono);
        fila.appendChild(cuerpo);
        lista.appendChild(fila);
    });
}

function alternarPanel() {
    if (!panel) return;

    panelAbierto = !panelAbierto;
    panel.classList.toggle('open', panelAbierto);

    if (campana) campana.setAttribute('aria-expanded', panelAbierto ? 'true' : 'false');

    if (panelAbierto) {
        pintarPanel();
        refrescar(true);
    }
}

function cerrarPanel() {
    if (!panel || !panelAbierto) return;
    panelAbierto = false;
    panel.classList.remove('open');
    if (campana) campana.setAttribute('aria-expanded', 'false');
}

/* ------------------------------------------------------------------ */
/* Datos                                                                */
/* ------------------------------------------------------------------ */
/**
 * Vuelve a pedir las notificaciones.
 *
 * @param {boolean} inicial  true en cargas iniciales/visibles: no hace
 *                            "ping" aunque entren avisos nuevos.
 */
async function refrescar(inicial) {
    if (!wrap || !estoyOnline()) return;

    try {
        const res = await fetch(`${URL_INDEX}?limite=${LIMITE}`, {
            headers: cabeceras(false),
            credentials: 'same-origin',
        });

        if (!res.ok) return;

        const data = await res.json();
        const antes = noLeidas;
        noLeidas = Number(data.no_leidas || 0);
        items = Array.isArray(data.notificaciones) ? data.notificaciones : [];

        const hayNuevas = !inicial && noLeidas > antes;
        pintarCampana(hayNuevas);
        if (hayNuevas) sonarAlerta();
        if (panelAbierto) pintarPanel();
    } catch (e) {
        // Sin conexión o error puntual: se conserva lo que ya se pintó.
    }
}

function marcarLeida(id) {
    if (!id) return Promise.resolve();

    return fetch(`${URL_INDEX}/${id}/leer`, {
        method: 'POST',
        headers: cabeceras(true),
        credentials: 'same-origin',
        body: '{}',
    }).then(() => refrescar(true)).catch(() => {});
}

function abrirNotificacion(id) {
    marcarLeida(id);

    const item = items.find((n) => n.id === id);
    if (item && item.url) {
        window.location.href = item.url;
        return;
    }

    cerrarPanel();
}

function leerTodas() {
    fetch(URL_LEER_TODAS, {
        method: 'POST',
        headers: cabeceras(true),
        credentials: 'same-origin',
        body: '{}',
    })
        .then(() => {
            items.forEach((n) => { n.leida = true; });
            noLeidas = 0;
            pintarCampana(false);
            pintarPanel();
        })
        .catch(() => {});
}

function limpiar() {
    fetch(URL_INDEX, {
        method: 'DELETE',
        headers: cabeceras(false),
        credentials: 'same-origin',
    })
        .then(() => {
            items = [];
            noLeidas = 0;
            pintarCampana(false);
            pintarPanel();
        })
        .catch(() => {});
}

/* ------------------------------------------------------------------ */
/* Init                                                                 */
/* ------------------------------------------------------------------ */
function init() {
    // Sólo el personal recibe notificaciones; los clientes no cargan la campana.
    if (!esPersonal()) return;
    if (!document.body) return;

    capturarPestana();

    // Desbloquea el audio en el primer gesto del usuario (política de autoplay).
    ['pointerdown', 'keydown', 'touchstart'].forEach((evento) => {
        document.addEventListener(evento, desbloquearAudio, { once: true, passive: true });
    });

    montar();
    if (!wrap) return;

    suscribirEcho();
    refrescar(true);

    estadoPush().then((estado) => {
        pushActivo = estado === 'activo';
    });

    // Sondeo en segundo plano: aquí sí se avisa (ping) si entran avisos.
    setInterval(() => refrescar(false), POLL_MS);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refrescar(true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
