import db from './db';
import { loadSnapshot, enqueuePedidoEstado, enqueuePagoMesa, enqueueCerrarMesa, probeOnline, syncNow } from './offline';

const SIGUIENTES = {
    pendiente: ['preparando', 'listo', 'servido'],
    preparando: ['listo', 'servido'],
    listo: ['servido'],
    servido: [],
};

let root = null;
let sesion = null;
let snapshot = { grupos: [] };
let ops = [];
let mesaModal = null;

function esc(valor) {
    return String(valor == null ? '' : valor).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[c]));
}

function dinero(valor, decimales) {
    const n = Number(valor || 0);
    const d = decimales || 0;
    return '$' + n.toLocaleString('es-CL', { minimumFractionDigits: d, maximumFractionDigits: d });
}

function titulo(estado) {
    return estado ? estado.charAt(0).toUpperCase() + estado.slice(1) : '';
}

function claseEstado(estado) {
    if (estado === 'pendiente') return 'ame';
    if (estado === 'preparando') return 'azu';
    if (estado === 'listo' || estado === 'servido' || estado === 'pagado') return 'ver';
    return 'gri';
}

function badge(estado) {
    return '<span class="ws-badge ' + claseEstado(estado) + '">' + titulo(estado) + '</span>';
}

function redondear(valor) {
    return Math.round(Number(valor || 0) * 100) / 100;
}

function recalcular(grupo) {
    const total = grupo.lineas.reduce((suma, linea) => suma + Number(linea.subtotal || 0), 0);
    const pagado = grupo.lineas.filter((l) => l.pagado).reduce((suma, linea) => suma + Number(linea.subtotal || 0), 0);
    grupo.total = redondear(total);
    grupo.pagado = redondear(pagado);
    grupo.pendiente = redondear(total - pagado);
    grupo.completa = grupo.pendiente <= 0;
}

function grupoDeMesa(mesaId) {
    return snapshot.grupos.find((g) => g.mesa && g.mesa.id === mesaId) || null;
}

function persistir() {
    return db.putSnapshot('pedidos', snapshot);
}

function marcarEstado(pedidoId, estado) {
    snapshot.grupos.forEach((grupo) => {
        const pedido = grupo.pedidos.find((p) => p.id === pedidoId);
        if (pedido) pedido.estado = estado;
    });
}

function marcarPago(mesaId, modo, lineas) {
    const grupo = grupoDeMesa(mesaId);
    if (!grupo) return;
    grupo.lineas.forEach((linea) => {
        if (!linea.pagado && (modo === 'total' || lineas.indexOf(linea.id) !== -1)) {
            linea.pagado = true;
        }
    });
    recalcular(grupo);
}

function marcarCierre(mesaId) {
    const grupo = grupoDeMesa(mesaId);
    if (!grupo) return;
    grupo.pedidos.forEach((pedido) => { pedido.estado = 'pagado'; });
    recalcular(grupo);
}

function acciones(grupo) {
    const mesaId = grupo.mesa ? grupo.mesa.id : null;
    const pagosEnCola = mesaId === null ? 0 : ops.filter((op) => op.type === 'pago_mesa' && op.mesa_id === mesaId).length;
    const cierreEnCola = mesaId === null ? false : ops.some((op) => op.type === 'cerrar_mesa' && op.mesa_id === mesaId);
    const partes = grupo.pedidos.map((pedido) => {
        const pendiente = ops.find((op) => op.type === 'pedido_estado' && op.pedido_id === pedido.id);
        const opciones = SIGUIENTES[pedido.estado] || [];
        const selector = opciones.length
            ? '<select data-pedido="' + pedido.id + '" data-estado="' + esc(pedido.estado) + '" aria-label="Cambiar estado del pedido ' + pedido.id + '">' +
                '<option value="">Cambiar estado…</option>' +
                opciones.map((estado) => '<option value="' + estado + '">' + titulo(estado) + '</option>').join('') +
                '</select>'
            : '';
        const aviso = pendiente
            ? '<span class="ws-cola">En cola → ' + titulo(pendiente.estado) + '</span>'
            : '';
        return '<div class="ws-pedido">' +
            '<span class="ws-pedido-id">#' + pedido.id + '</span>' + badge(pedido.estado) + selector + aviso +
            '</div>';
    }).join('');

    const botones = [];
    if (mesaId !== null) {
        if (!grupo.completa && !pagosEnCola) {
            botones.push('<button type="button" class="ws-btn ws-btn-primario" data-pago="' + mesaId + '">Pagar</button>');
        }
        if (grupo.completa && !cierreEnCola) {
            botones.push('<button type="button" class="ws-btn" data-cerrar="' + mesaId + '">Cerrar mesa</button>');
        }
    }

    return '<div class="ws-acciones">' + partes + (botones.length ? '<div class="ws-botones">' + botones.join('') + '</div>' : '') + '</div>';
}

function modalPago() {
    const grupo = mesaModal === null ? null : grupoDeMesa(mesaModal);
    if (!grupo || !grupo.mesa) return '';
    const lineas = grupo.lineas.filter((linea) => !linea.pagado);
    if (!lineas.length) return '';

    return '<div class="ws-overlay">' +
        '<form class="ws-modal" data-pago-form="' + grupo.mesa.id + '">' +
        '<h3>Cobrar mesa ' + esc(grupo.mesa.numero) + '</h3>' +
        '<p>El cobro queda registrado en este dispositivo y se envía al servidor al volver la conexión.</p>' +
        '<label>Método de pago' +
        '<select name="metodo_pago">' +
        '<option value="efectivo">Efectivo</option>' +
        '<option value="tarjeta">Tarjeta</option>' +
        '<option value="qr">QR</option>' +
        '<option value="otro">Otro</option>' +
        '</select></label>' +
        '<label>Modo' +
        '<select name="modo">' +
        '<option value="total">Total de la mesa</option>' +
        '<option value="productos">Productos seleccionados</option>' +
        '</select></label>' +
        '<div class="ws-lineas">' + lineas.map((linea) =>
            '<label><input type="checkbox" name="lineas[]" value="' + linea.id + '"> ' +
            esc(linea.cantidad) + '× ' + esc(linea.producto) + ' · ' + dinero(linea.subtotal, 2) + '</label>'
        ).join('') + '</div>' +
        '<div class="ws-modal-acciones">' +
        '<button type="submit" class="ws-btn ws-btn-primario">Registrar pago</button>' +
        '<button type="button" class="ws-btn" data-cerrar-modal>Cancelar</button>' +
        '</div></form></div>';
}

function render() {
    if (!sesion || !sesion.is_staff) return;

    const grupos = snapshot.grupos || [];
    const filas = grupos.length
        ? grupos.map((grupo) => {
            const estados = Array.from(new Set(grupo.pedidos.map((p) => p.estado)))
                .map((estado) => badge(estado)).join(' ');
            const estadoPago = grupo.completa
                ? '<span class="ws-pago ok">Pagado</span>'
                : '<span class="ws-pago pend">Pendiente ' + dinero(grupo.pendiente, 2) + '</span>';
            return '<tr>' +
                '<td><strong>' + (grupo.mesa ? 'Mesa ' + esc(grupo.mesa.numero) : 'Sin mesa') + '</strong></td>' +
                '<td>' + grupo.pedidos.length + ' pedido(s)</td>' +
                '<td>' + estados + '</td>' +
                '<td><strong>' + dinero(grupo.total) + '</strong><div>' + estadoPago + '</div></td>' +
                '<td>' + acciones(grupo) + '</td>' +
                '</tr>';
        }).join('')
        : '<tr><td colspan="5" class="ws-vacio">No hay pedidos activos en el snapshot. Conecta una vez para descargarlos.</td></tr>';

    root.innerHTML =
        '<div class="ws-head">' +
        '<div><h1>Modo offline · personal</h1>' +
        '<p>Puedes tomar pedidos, cambiar estados y cobrar. Todo se guarda aquí y se envía cuando vuelva la conexión.</p></div>' +
        '<div class="ws-head-acciones">' +
        '<span class="ws-contador">' + ops.length + ' cambio(s) en espera</span>' +
        '<a class="ws-btn ws-btn-primario" href="/pedidos/create">Nuevo pedido</a>' +
        '<button type="button" class="ws-btn" data-reintentar>Reintentar conexión</button>' +
        '</div></div>' +
        '<div class="ws-tabla"><table><thead><tr>' +
        '<th>Mesa</th><th>Pedidos</th><th>Estados</th><th>Total</th><th style="text-align:right">Acciones</th>' +
        '</tr></thead><tbody>' + filas + '</tbody></table></div>' +
        modalPago();
}

function cargar() {
    return Promise.all([
        loadSnapshot('session'),
        loadSnapshot('pedidos'),
        db.getAllOps(),
    ]).then((resultados) => {
        sesion = resultados[0] || null;
        snapshot = resultados[1] || { grupos: [] };
        ops = (resultados[2] || []).slice().sort((a, b) => (a.created_at || 0) - (b.created_at || 0));

        if (!sesion || !sesion.is_staff) {
            root.style.display = 'none';
            document.body.classList.remove('tiene-workspace');
            const tarjeta = document.querySelector('.card');
            if (tarjeta) tarjeta.style.display = '';
            return;
        }

        document.body.classList.add('tiene-workspace');
        const tarjeta = document.querySelector('.card');
        if (tarjeta) tarjeta.style.display = 'none';
        root.style.display = 'block';
        render();
    }).catch(() => {});
}

function init() {
    root = document.getElementById('offline-workspace');
    if (!root) return;

    window.__pwaEsPos = true;
    cargar();

    const anterior = window.__offlineOnChange;
    window.__offlineOnChange = () => {
        if (anterior) anterior();
        cargar();
    };
    window.addEventListener('app:data-refreshed', cargar);

    root.addEventListener('change', (evento) => {
        const select = evento.target;
        if (!select.matches || !select.matches('select[data-pedido]')) return;
        const pedidoId = Number(select.dataset.pedido);
        const actual = select.dataset.estado;
        const destino = select.value;
        if (!destino) return;
        if ((SIGUIENTES[actual] || []).indexOf(destino) === -1) {
            render();
            return;
        }
        enqueuePedidoEstado(pedidoId, destino, actual)
            .then(() => {
                marcarEstado(pedidoId, destino);
                return persistir();
            })
            .then(cargar)
            .catch(cargar);
    });

    root.addEventListener('click', (evento) => {
        if (!evento.target.closest) return;
        const pago = evento.target.closest('[data-pago]');
        if (pago) {
            mesaModal = Number(pago.dataset.pago);
            render();
            return;
        }
        const cerrar = evento.target.closest('[data-cerrar]');
        if (cerrar) {
            const mesaId = Number(cerrar.dataset.cerrar);
            enqueueCerrarMesa(mesaId)
                .then(() => {
                    marcarCierre(mesaId);
                    return persistir();
                })
                .then(cargar)
                .catch(cargar);
            return;
        }
        if (evento.target.closest('[data-cerrar-modal]')) {
            mesaModal = null;
            render();
            return;
        }
        const reintentar = evento.target.closest('[data-reintentar]');
        if (reintentar) {
            reintentar.disabled = true;
            probeOnline().then((ok) => {
                if (ok) return syncNow(true);
                reintentar.disabled = false;
                render();
                return null;
            }).catch(() => {
                reintentar.disabled = false;
                render();
            });
        }
    });

    root.addEventListener('submit', (evento) => {
        const form = evento.target;
        if (!form.matches || !form.matches('form[data-pago-form]')) return;
        evento.preventDefault();
        const mesaId = Number(form.dataset.pagoForm);
        const datos = new FormData(form);
        const modo = datos.get('modo');
        const lineas = datos.getAll('lineas[]').map(Number);
        if (modo === 'productos' && !lineas.length) {
            window.alert('Selecciona al menos un producto.');
            return;
        }
        enqueuePagoMesa(mesaId, {
            metodo_pago: datos.get('metodo_pago'),
            modo: modo,
            lineas: lineas,
        }).then(() => {
            marcarPago(mesaId, modo, lineas);
            return persistir();
        }).then(() => {
            mesaModal = null;
            return cargar();
        }).catch(cargar);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
