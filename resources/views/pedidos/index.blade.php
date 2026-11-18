@extends('layouts.admin')

@section('content')
    <style>
        tr.pedido-grupo{cursor:pointer;}
        tr.pedido-grupo:hover td{background:var(--bg-4);}
        .pedido-grupo-detalle > td{background:var(--bg-2);padding:0 !important;}
        .pedido-grupo-detalle table{border-top:1px solid var(--border-dim);}
        .pedido-grupo-detalle thead tr{background:transparent;}
        .pedido-grupo-detalle tbody tr:last-child td{border-bottom:none;}
        .mesa-view-tab .nav-chip{margin-left:0;}
        .linea-chips{display:flex;flex-wrap:wrap;gap:6px;padding:2px 12px 10px;}
        .linea-chip{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;padding:4px 9px;border-radius:99px;border:1px solid var(--border-dim);background:var(--bg-3);color:var(--text-2);}
        .linea-chip .lc-sub{font-weight:600;color:var(--text-1);}
        .linea-chip.pagado{border-color:rgba(76,175,80,.35);background:var(--green-bg);color:var(--green-text);}
        .linea-chip.pagado .lc-sub{color:var(--green-text);}
        .pago-estado{font-size:11px;margin-top:3px;}
        .pago-estado.pend{color:var(--amber-text);}
        .pago-estado.ok{color:var(--green-text);}
        .pagar-modal{max-width:560px;}
        .pagar-resumen{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:14px 18px;border-bottom:1px solid var(--border-dim);}
        .pagar-resumen div{background:var(--bg-4);border:1px solid var(--border-dim);border-radius:var(--radius);padding:8px 10px;text-align:center;}
        .pagar-resumen span{display:block;font-size:10.5px;color:var(--text-4);text-transform:uppercase;letter-spacing:.4px;}
        .pagar-resumen strong{font-size:15px;color:var(--text-1);}
        .pagar-tabs{display:flex;gap:6px;padding:14px 18px 0;}
        .pagar-tab{flex:1;padding:8px 6px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-4);color:var(--text-3);font-size:12px;font-family:inherit;cursor:pointer;transition:all .12s;}
        .pagar-tab.active{background:var(--gold-glow);border-color:var(--gold);color:var(--gold-light);font-weight:600;}
        .pagar-panel{padding:14px 18px;font-size:13px;color:var(--text-2);}
        .pagar-lista{max-height:260px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;}
        .pg-linea{display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--border-dim);border-radius:var(--radius);background:var(--bg-4);cursor:pointer;font-size:12.5px;color:var(--text-2);}
        .pg-linea:hover{border-color:var(--border);}
        .pg-linea input{accent-color:var(--gold);}
        .pg-linea .pg-nombre{flex:1;}
        .pg-linea .pg-sub{font-weight:600;color:var(--text-1);}
        .pagar-seleccion{margin-top:10px;font-size:13px;color:var(--text-3);text-align:right;}
        .pagar-seleccion strong{color:var(--gold-light);}
        .pg-partes-ctrl{display:flex;align-items:center;gap:8px;}
        .pg-partes-ctrl .pg-step{width:34px;height:34px;border-radius:var(--radius);border:1px solid var(--border-dim);background:var(--bg-4);color:var(--text-1);font-size:16px;cursor:pointer;}
        .pg-reparto{margin-top:12px;display:flex;flex-direction:column;gap:8px;max-height:240px;overflow-y:auto;}
        .pg-persona{border:1px solid var(--border-dim);border-radius:var(--radius);background:var(--bg-4);padding:10px 12px;}
        .pg-persona-head{display:flex;justify-content:space-between;font-size:12.5px;font-weight:600;color:var(--text-1);margin-bottom:6px;}
        .pg-persona-head span:last-child{color:var(--gold-light);}
        .pg-persona ul{margin:0;padding-left:16px;font-size:11.5px;color:var(--text-3);}
        .pagar-campo{padding:0 18px 14px;}
        .pagar-campo label{display:block;font-size:11.5px;color:var(--text-3);margin-bottom:6px;}
        .pagar-footer{display:flex;justify-content:flex-end;gap:8px;padding:14px 18px;border-top:1px solid var(--border-dim);}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);}
        .modal-content{background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);width:90%;max-width:780px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;}
        .modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border-dim);font-size:15px;font-weight:600;color:var(--text-1);}
        .modal-close{background:none;border:none;font-size:22px;color:var(--text-3);cursor:pointer;padding:0 4px;line-height:1;}
        .modal-close:hover{color:var(--text-1);}
    </style>
    <div class="page-top">
        <div>
            <h1 class="page-title">Pedidos</h1>
            <p class="page-sub">@if ($tab === 'historial') Historial de pedidos pagados y cancelados @else Gestiona los pedidos de las mesas @endif</p>
        </div>
        <div class="btn-row">
            @if ($tab === 'activos')
                <a href="{{ route('pedidos.create') }}" class="btn btn-gold">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="pedidos-contenido" data-page="pedidos" data-tab="{{ $tab }}" data-fragmento-url="{{ route('pedidos.fragmento') }}">
        @include('pedidos._contenido')
    </div>

    <div class="modal-overlay" id="pagarModal" style="display:none;">
        <div class="modal-content pagar-modal">
            <div class="modal-header">
                <span id="pgTitulo"><i class="fas fa-cash-register"></i> Pagar mesa</span>
                <button type="button" class="modal-close" onclick="cerrarPagar()">&times;</button>
            </div>
            <div class="pagar-resumen">
                <div><span>Total</span><strong id="pgTotal">$0</strong></div>
                <div><span>Pagado</span><strong id="pgPagado">$0</strong></div>
                <div><span>Pendiente</span><strong id="pgPendiente">$0</strong></div>
            </div>
            <div class="pagar-tabs">
                <button type="button" class="pagar-tab active" data-modo="total">Pago total</button>
                <button type="button" class="pagar-tab" data-modo="productos">Elegir productos</button>
                <button type="button" class="pagar-tab" data-modo="partes">Dividir en partes</button>
            </div>
            <form id="pagarForm" method="POST" action="{{ route('pedidos.pagarMesa', ['mesa' => '__MESA__']) }}">
                @csrf
                <input type="hidden" name="modo" id="pgModo" value="total">
                <input type="hidden" name="partes" id="pgPartesInput" value="">

                <div class="pagar-panel" id="panel-total">
                    Se pagará todo lo pendiente de la mesa:
                    <strong id="pgTotalPanel" style="color:var(--gold-light);"></strong>
                </div>

                <div class="pagar-panel" id="panel-productos" style="display:none;">
                    <div class="pagar-lista" id="pgListaProductos"></div>
                    <div class="pagar-seleccion">Seleccionado: <strong id="pgSeleccionado">$0.00</strong></div>
                </div>

                <div class="pagar-panel" id="panel-partes" style="display:none;">
                    <div class="pg-partes-ctrl">
                        <button type="button" class="pg-step" onclick="pgPartes(-1)">&minus;</button>
                        <input type="number" class="form-input" id="pgNPartes" value="2" min="2" max="30" readonly style="width:64px;text-align:center;">
                        <button type="button" class="pg-step" onclick="pgPartes(1)">+</button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="pgCalcular()"><i class="fas fa-calculator"></i> Calcular reparto</button>
                    </div>
                    <div class="pg-reparto" id="pgReparto"></div>
                </div>

                <div class="pagar-campo">
                    <label for="pgMetodo">Método de pago</label>
                    <select name="metodo_pago" id="pgMetodo" class="form-select">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="qr">QR / Transferencia</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </form>
            <div id="pgError" class="alert alert-error" style="display:none;margin:0 18px 14px;"></div>
            <div class="pagar-footer">
                <button type="button" class="btn btn-ghost" onclick="cerrarPagar()">Cancelar</button>
                <button type="submit" form="pagarForm" class="btn btn-gold"><i class="fas fa-check"></i> Pagar</button>
            </div>
        </div>
    </div>

    <script>
    var GRUPOS_PG = @json($gruposPago);
    var PAGAR_URL = @json(route('pedidos.pagarMesa', ['mesa' => '__MESA__']));
    var pgIndice = null;
    var pgModo = 'total';
    var pgRepartoOk = false;

    function fmtM(n) {
        return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function pgEsc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function pgMostrarError(msg) {
        var el = document.getElementById('pgError');
        el.textContent = msg;
        el.style.display = '';
    }

    function pgLimpiarError() {
        var el = document.getElementById('pgError');
        el.textContent = '';
        el.style.display = 'none';
    }

    function togglePedidoGrupo(indice) {
        var detalle = document.querySelector('.pedido-grupo-detalle[data-detalle="' + indice + '"]');
        if (!detalle) return;
        document.querySelectorAll('.pedido-grupo[data-indice="' + indice + '"] .pedido-grupo-icon')
            .forEach(function (icon) { icon.style.transform = detalle.style.display === 'none' ? 'rotate(90deg)' : ''; });
        detalle.style.display = detalle.style.display === 'none' ? '' : 'none';
    }

    function abrirPagar(indice) {
        var g = GRUPOS_PG[indice];
        if (!g || !g.mesa) return;
        pgIndice = indice;

        document.getElementById('pgTitulo').innerHTML = '<i class="fas fa-cash-register"></i> Pagar - Mesa ' + pgEsc(g.mesa.numero);
        document.getElementById('pgTotal').textContent = fmtM(g.total);
        document.getElementById('pgPagado').textContent = fmtM(g.pagado);
        document.getElementById('pgPendiente').textContent = fmtM(g.pendiente);
        document.getElementById('pgTotalPanel').textContent = fmtM(g.pendiente);
        document.getElementById('pagarForm').action = PAGAR_URL.replace('__MESA__', g.mesa.id);

        var lista = document.getElementById('pgListaProductos');
        lista.innerHTML = g.lineas.map(function (l) {
            return '<label class="pg-linea">'
                + '<input type="checkbox" class="pg-check" value="' + l.id + '" data-sub="' + l.subtotal + '">'
                + '<span class="pg-nombre">Pedido #' + pgEsc(l.pedido_id) + ' &middot; ' + pgEsc(l.cantidad) + '&times; ' + pgEsc(l.producto) + '</span>'
                + '<span class="pg-sub">' + fmtM(l.subtotal) + '</span>'
                + '</label>';
        }).join('');
        lista.querySelectorAll('.pg-check').forEach(function (c) {
            c.addEventListener('change', pgActualizarSeleccion);
        });
        pgActualizarSeleccion();

        pgSetModo('total');
        document.getElementById('pgNPartes').value = 2;
        document.getElementById('pgReparto').innerHTML = '';
        pgRepartoOk = false;
        pgLimpiarError();

        document.getElementById('pagarModal').style.display = 'flex';
    }

    function cerrarPagar() {
        document.getElementById('pagarModal').style.display = 'none';
    }

    function pgSetModo(modo) {
        pgModo = modo;
        document.querySelectorAll('.pagar-tab').forEach(function (t) {
            t.classList.toggle('active', t.dataset.modo === modo);
        });
        ['total', 'productos', 'partes'].forEach(function (m) {
            document.getElementById('panel-' + m).style.display = (m === modo) ? '' : 'none';
        });
    }

    document.querySelectorAll('.pagar-tab').forEach(function (t) {
        t.addEventListener('click', function () { pgSetModo(t.dataset.modo); pgLimpiarError(); });
    });

    function pgActualizarSeleccion() {
        var sum = 0;
        document.querySelectorAll('#pgListaProductos .pg-check').forEach(function (c) {
            if (c.checked) sum += parseFloat(c.dataset.sub);
        });
        document.getElementById('pgSeleccionado').textContent = fmtM(sum);
    }

    function pgPartes(delta) {
        var inp = document.getElementById('pgNPartes');
        inp.value = Math.min(30, Math.max(2, (parseInt(inp.value, 10) || 2) + delta));
        pgRepartoOk = false;
        document.getElementById('pgReparto').innerHTML = '';
    }

    function pgMontos(pendiente, n) {
        var cents = Math.round(pendiente * 100);
        var base = Math.floor(cents / n);
        var residuo = cents - base * n;
        var montos = [];
        for (var i = 0; i < n; i++) montos.push(base);
        montos[n - 1] = base + residuo;
        return montos.map(function (c) { return c / 100; });
    }

    function pgCalcular() {
        var g = GRUPOS_PG[pgIndice];
        var n = parseInt(document.getElementById('pgNPartes').value, 10) || 2;
        var montos = pgMontos(g.pendiente, n);
        pgRepartoOk = true;

        var items = g.lineas.map(function (l) {
            return '<li>' + pgEsc(l.cantidad) + '&times; ' + pgEsc(l.producto) + ' <span style="float:right;">' + fmtM(l.subtotal) + '</span></li>';
        }).join('');

        document.getElementById('pgReparto').innerHTML =
            '<div class="pg-nota" style="margin-bottom:8px;color:var(--text-2);font-size:13px;">'
            + 'Reparto de ' + fmtM(g.pendiente) + ' en ' + n + ' partes iguales. '
            + 'Los productos se muestran como referencia; cada quien paga su parte del total.'
            + '</div>'
            + montos.map(function (monto, i) {
                return '<div class="pg-persona">'
                    + '<div class="pg-persona-head"><span>Persona ' + (i + 1) + '</span><span>' + fmtM(monto) + '</span></div>'
                    + '<ul>' + items + '</ul>'
                    + '<div style="border-top:1px dashed var(--border-dim);margin-top:6px;padding-top:6px;font-size:13px;color:var(--text-2);">'
                    + 'Parte del total pagado: <strong style="color:var(--gold-light);">' + fmtM(monto) + '</strong> '
                    + 'de ' + fmtM(g.pendiente) + ' (' + n + ' personas)'
                    + '</div>'
                    + '</div>';
            }).join('');
    }

    document.getElementById('pagarForm').addEventListener('submit', function (e) {
        var form = this;
        form.querySelectorAll('input[name="lineas[]"]').forEach(function (i) { i.remove(); });
        document.getElementById('pgModo').value = pgModo;
        document.getElementById('pgPartesInput').value = pgModo === 'partes'
            ? document.getElementById('pgNPartes').value : '';

        if (pgModo === 'productos') {
            var checks = form.querySelectorAll('#pgListaProductos .pg-check:checked');
            if (!checks.length) {
                e.preventDefault();
                pgMostrarError('Selecciona al menos un producto sin pagar.');
                return;
            }
            checks.forEach(function (c) {
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'lineas[]';
                h.value = c.value;
                form.appendChild(h);
            });
        }

        if (pgModo === 'partes' && !pgRepartoOk) {
            e.preventDefault();
            pgMostrarError('Calcula primero el reparto por partes.');
        }
    });

    document.getElementById('pagarModal').addEventListener('click', function (e) {
        if (e.target === this) cerrarPagar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('pagarModal').style.display !== 'none') {
            cerrarPagar();
        }
    });
    </script>
@endsection