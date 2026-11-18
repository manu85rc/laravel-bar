@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Mesas</div>
        <div class="page-sub">{{ $mesas->count() }} mesas registradas · {{ $zonaActual ? $zonas->firstWhere('id', $zonaActual)?->nombre : 'Todas las zonas' }}</div>
    </div>
    <div class="btn-row">
        <button type="button" class="btn btn-ghost" onclick="abrirModalZona()"><i class="fa-solid fa-plus"></i> Nueva Zona</button>
        @if ($tab === 'plano')
            <button class="btn btn-ghost" onclick="guardarPlanos()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        @endif
        <a href="{{ route('admin.mesas.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nueva Mesa</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

<div class="mesa-view-tabs">
    <a href="{{ route('admin.mesas.index', array_merge(request()->query(), ['tab' => 'plano'])) }}" class="mesa-view-tab {{ $tab === 'plano' ? 'active' : '' }}"><i class="fa-solid fa-table-cells"></i> Plano</a>
    <a href="{{ route('admin.mesas.index', array_merge(request()->query(), ['tab' => 'lista'])) }}" class="mesa-view-tab {{ $tab === 'lista' ? 'active' : '' }}"><i class="fa-solid fa-list"></i> Lista</a>
</div>

@if ($tab === 'plano')
    {{-- ========== TAB PLANO ========== --}}
    @if ($zonas->isNotEmpty())
    <div class="zona-tabs" id="zona-tabs">
        <div id="zona-tab-links">
            @foreach ($zonas as $z)
                <a href="{{ route('admin.mesas.index', ['tab' => 'plano', 'zona' => $z->id]) }}" class="zona-tab {{ $z->id == $zonaActual ? 'active' : '' }}" data-zona-id="{{ $z->id }}" data-original-nombre="{{ $z->nombre }}" ondragstart="startZonaTabDrag(event)" onclick="return zonaTabClick(event, this)"><span class="zona-tab-handle" draggable="true"><i class="fa-solid fa-grip-vertical"></i></span><span class="zona-tab-name">{{ $z->nombre }}</span><span class="zona-tab-close" onclick="eliminarZonaTab(event, this)" title="Eliminar zona">&times;</span></a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="plano-toolbar" id="plano-toolbar">
        <div class="toolbar-group">
            <label class="toolbar-label">Forma</label>
            <div class="shape-selector">
                <button class="shape-btn active" data-shape="circle" onclick="selectShape('circle')" title="Círculo">
                    <div class="shape-preview circle"></div>
                </button>
                <button class="shape-btn" data-shape="square" onclick="selectShape('square')" title="Cuadrado">
                    <div class="shape-preview square"></div>
                </button>
            </div>
        </div>
        <div class="toolbar-group">
            <label class="toolbar-label">Ancho</label>
            <input type="range" class="size-slider" id="ancho-slider" min="60" max="180" value="80" oninput="updateSize()">
            <input type="number" class="size-input" id="ancho-input" min="60" max="180" value="80" onchange="updateSize()">
        </div>
        <div class="toolbar-group">
            <label class="toolbar-label">Alto</label>
            <input type="range" class="size-slider" id="alto-slider" min="60" max="180" value="80" oninput="updateSize()">
            <input type="number" class="size-input" id="alto-input" min="60" max="180" value="80" onchange="updateSize()">
        </div>
        <div class="toolbar-group">
            <label class="toolbar-label">Color</label>
            <div class="color-palette" id="color-palette">
                <button class="color-swatch clear-btn" data-color="" onclick="selectColor(null)" title="Color por defecto">✕</button>
                <button class="color-swatch" data-color="#2d8f5e" style="background:#2d8f5e" onclick="selectColor('#2d8f5e')" title="Verde"></button>
                <button class="color-swatch" data-color="#3a7bbd" style="background:#3a7bbd" onclick="selectColor('#3a7bbd')" title="Azul"></button>
                <button class="color-swatch" data-color="#7c4dff" style="background:#7c4dff" onclick="selectColor('#7c4dff')" title="Púrpura"></button>
                <button class="color-swatch" data-color="#e67e22" style="background:#e67e22" onclick="selectColor('#e67e22')" title="Naranja"></button>
                <button class="color-swatch" data-color="#e74c8b" style="background:#e74c8b" onclick="selectColor('#e74c8b')" title="Rosa"></button>
                <button class="color-swatch" data-color="#1abc9c" style="background:#1abc9c" onclick="selectColor('#1abc9c')" title="Turquesa"></button>
                <button class="color-swatch" data-color="#f1c40f" style="background:#f1c40f" onclick="selectColor('#f1c40f')" title="Amarillo"></button>
                <button class="color-swatch" data-color="#95a5a6" style="background:#95a5a6" onclick="selectColor('#95a5a6')" title="Gris"></button>
                <button class="color-swatch" data-color="#e74c3c" style="background:#e74c3c" onclick="selectColor('#e74c3c')" title="Rojo"></button>
                <button class="color-swatch" data-color="#1a1a2e" style="background:#1a1a2e" onclick="selectColor('#1a1a2e')" title="Negro"></button>
            </div>
        </div>
        <div class="toolbar-group">
            <span class="toolbar-label" style="opacity:.5">Mesa <span id="sel-num">—</span></span>
        </div>
    </div>

    <div class="plano-wrapper">
        <div class="plano-canvas" id="plano-canvas">
            <div class="plano-stage" id="plano-stage">
            @foreach ($mesas as $mesa)
                @php
                    $estado = 'free';
                    if (in_array($mesa->id, $mesasConPedido)) { $estado = 'occupied'; }
                    elseif (in_array($mesa->id, $mesasConReserva)) { $estado = 'reserved'; }
                    $label = match($estado) {
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        default => 'Libre',
                    };
                @endphp
                <div class="mesa-shape {{ $estado }} {{ $mesa->forma }} {{ $estado === 'free' && $mesa->color ? 'custom-color' : '' }}"
                     data-id="{{ $mesa->id }}"
                     data-numero="{{ $mesa->numero }}"
                     data-estado="{{ $estado }}"
                     data-label="{{ $label }}"
                     data-color="{{ $mesa->color ?? '' }}"
                     @if($estado === 'free' && $mesa->color)
                         style="left:{{ $mesa->pos_x }}px; top:{{ $mesa->pos_y }}px; width:{{ $mesa->ancho }}px; height:{{ $mesa->alto }}px; background:{{ $mesa->color }}; color:{{ $mesa->color === '#f1c40f' || $mesa->color === '#1abc9c' || $mesa->color === '#95a5a6' ? '#1a1816' : '#fff' }};"
                     @else
                         style="left:{{ $mesa->pos_x }}px; top:{{ $mesa->pos_y }}px; width:{{ $mesa->ancho }}px; height:{{ $mesa->alto }}px;"
                     @endif>
                    <div class="mesa-label">{{ $mesa->numero }}</div>
                </div>
            @endforeach
            </div>
        </div>
    </div>

    <div class="plano-status" id="plano-status"></div>

@else
    {{-- ========== TAB LISTA ========== --}}
    @if ($zonas->isNotEmpty())
    <div class="filter-bar">
        <div class="filter-group">
            <label>Filtrar por zona</label>
            <select class="form-select" onchange="if(this.value) window.location=this.value;">
                <option value="{{ route('admin.mesas.index', ['tab' => 'lista']) }}" {{ !$zonaActual ? 'selected' : '' }}>Todas</option>
                @foreach ($zonas as $z)
                    <option value="{{ route('admin.mesas.index', ['tab' => 'lista', 'zona' => $z->id]) }}" {{ $z->id == $zonaActual ? 'selected' : '' }}>{{ $z->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    @if ($mesas->isEmpty())
        <div class="empty-state">No hay mesas registradas{{ $zonaActual ? ' en esta zona' : '' }}.</div>
    @else
    <div class="card">
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Zona</th>
                        <th>Disponible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mesas as $mesa)
                        <tr>
                            <td><strong>Mesa {{ $mesa->numero }}</strong></td>
                            <td>{{ $mesa->zona?->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $mesa->disponible ? 'b-green' : 'b-red' }}">
                                    <span class="bdot"></span>{{ $mesa->disponible ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <div class="list-item-actions">
                                    <a href="{{ route('admin.mesas.edit', $mesa) }}">Editar</a>
                                    <form action="{{ route('admin.mesas.destroy', $mesa) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-link danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endif

<script>
var mesas = @json($mesasData);

var selectedId = null;
var dragging = false;
var dragEl = null;
var dragOffX = 0, dragOffY = 0;

var canvas = document.getElementById('plano-canvas');
var planoStage = document.getElementById('plano-stage');
var planoScale = 1;
var planoContentW = 0, planoContentH = 0;
var PLANO_PAD = 24;
var PLANO_MIN_SCALE = 0.55;
var PLANO_GRID = 20;
var PLANO_MIN_H = 600;

if (canvas) {
    var allAtZero = mesas.every(function(m) { return m.pos_x === 0 && m.pos_y === 0; });
    if (allAtZero) {
        var spacingX = 140;
        var spacingY = 120;
        var startX = 30, startY = 30;
        var availW = canvas.clientWidth || canvas.offsetWidth || 600;
        var cols = Math.max(1, Math.floor((availW - startX) / spacingX));
        mesas.forEach(function(m, i) {
            var col = i % cols;
            var row = Math.floor(i / cols);
            m.pos_x = startX + col * spacingX;
            m.pos_y = startY + row * spacingY;
        });
    }
}

function applyFitPlano() {
    if (!canvas || !planoStage || !mesas.length) return;
    canvas.style.minWidth = '';
    var contentW = 0, contentH = 0;
    mesas.forEach(function(m) {
        contentW = Math.max(contentW, m.pos_x + m.ancho);
        contentH = Math.max(contentH, m.pos_y + m.alto);
    });
    contentW = Math.max(contentW, 1);
    contentH = Math.max(contentH, 1);

    var availW = canvas.clientWidth;
    var desired = (availW - PLANO_PAD) / contentW;
    var scale = Math.min(1, Math.max(PLANO_MIN_SCALE, desired));
    planoScale = scale;

    // Área de trabajo arrastrable: cubre todo el contenido y toda la
    // superficie visible, para poder colocar mesas en los espacios vacíos.
    planoContentW = Math.max(contentW, availW / scale);
    planoContentH = Math.max(contentH, PLANO_MIN_H);

    planoStage.style.transform = 'scale(' + scale + ')';
    var altura = planoContentH * scale + PLANO_PAD;
    canvas.style.height = altura + 'px';
    canvas.style.minHeight = altura + 'px';
    canvas.style.setProperty('--plano-grid', (PLANO_GRID * scale) + 'px');

    if (contentW * scale + PLANO_PAD > availW) {
        canvas.style.minWidth = (contentW * scale + PLANO_PAD) + 'px';
    } else {
        canvas.style.minWidth = '';
    }
}

function textColorForBg(hex) {
    if (!hex) return '';
    var r = parseInt(hex.slice(1,3), 16);
    var g = parseInt(hex.slice(3,5), 16);
    var b = parseInt(hex.slice(5,7), 16);
    return (r * 0.299 + g * 0.587 + b * 0.114) > 160 ? '#1a1816' : '#fff';
}

function renderizarMesas() {
    if (!canvas) return;
    var shapes = canvas.querySelectorAll('.mesa-shape');
    var map = {};
    shapes.forEach(function(el) { map[el.dataset.id] = el; });
    mesas.forEach(function(m) {
        var el = map[m.id];
        if (el) {
            el.style.left = m.pos_x + 'px';
            el.style.top = m.pos_y + 'px';
            el.style.width = m.ancho + 'px';
            el.style.height = m.alto + 'px';
            el.className = 'mesa-shape ' + el.dataset.estado + ' ' + m.forma;
            el.dataset.color = m.color || '';
            if (selectedId == m.id) el.classList.add('selected');
            if (el.dataset.estado === 'free' && m.color) {
                el.classList.add('custom-color');
                el.style.background = m.color;
                el.style.color = textColorForBg(m.color);
            } else {
                el.classList.remove('custom-color');
                el.style.background = '';
                el.style.color = '';
            }
        }
    });
}

function seleccionarMesa(id) {
    if (selectedId === id) return;
    var all = canvas.querySelectorAll('.mesa-shape');
    all.forEach(function(el) { el.classList.remove('selected'); });
    selectedId = id;
    var mesa = mesas.find(function(m) { return m.id === id; });
    if (mesa) {
        document.getElementById('sel-num').textContent = mesa.numero;
        document.getElementById('ancho-slider').value = mesa.ancho;
        document.getElementById('ancho-input').value = mesa.ancho;
        document.getElementById('alto-slider').value = mesa.alto;
        document.getElementById('alto-input').value = mesa.alto;
        document.querySelectorAll('.shape-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.shape === mesa.forma);
        });
        document.querySelectorAll('#color-palette .color-swatch').forEach(function(sw) {
            var swColor = sw.dataset.color || null;
            var mesaColor = mesa.color || null;
            sw.classList.toggle('active', swColor === mesaColor);
        });
        var el = canvas.querySelector('.mesa-shape[data-id="' + id + '"]');
        if (el) el.classList.add('selected');
    }
}

function deseleccionar() {
    selectedId = null;
    document.getElementById('sel-num').textContent = '—';
    canvas.querySelectorAll('.mesa-shape').forEach(function(el) { el.classList.remove('selected'); });
    document.querySelectorAll('#color-palette .color-swatch').forEach(function(sw) { sw.classList.remove('active'); });
}

function selectColor(color) {
    if (selectedId === null) return;
    var mesa = mesas.find(function(m) { return m.id === selectedId; });
    if (!mesa) return;
    mesa.color = color;
    document.querySelectorAll('#color-palette .color-swatch').forEach(function(sw) {
        var swColor = sw.dataset.color || null;
        sw.classList.toggle('active', swColor === color);
    });
    renderizarMesas();
}

function selectShape(shape) {
    if (selectedId === null) return;
    var mesa = mesas.find(function(m) { return m.id === selectedId; });
    if (!mesa) return;
    mesa.forma = shape;
    document.querySelectorAll('.shape-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.shape === shape);
    });
    renderizarMesas();
}

function updateSize() {
    if (selectedId === null) return;
    var mesa = mesas.find(function(m) { return m.id === selectedId; });
    if (!mesa) return;
    var ancho = parseInt(document.getElementById('ancho-slider').value);
    var alto = parseInt(document.getElementById('alto-slider').value);
    document.getElementById('ancho-input').value = ancho;
    document.getElementById('alto-input').value = alto;
    document.getElementById('ancho-slider').value = ancho;
    document.getElementById('alto-slider').value = alto;
    mesa.ancho = ancho;
    mesa.alto = alto;
    renderizarMesas();
}

if (canvas) {
    canvas.addEventListener('mousedown', function(e) {
        var el = e.target.closest('.mesa-shape');
        if (!el) { deseleccionar(); return; }
        var id = parseInt(el.dataset.id);
        seleccionarMesa(id);
        var stageRect = planoStage.getBoundingClientRect();
        dragOffX = (e.clientX - stageRect.left) / planoScale - parseFloat(el.style.left || 0);
        dragOffY = (e.clientY - stageRect.top) / planoScale - parseFloat(el.style.top || 0);
        dragging = true;
        dragEl = el;
        el.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!dragging || !dragEl) return;
        var stageRect = planoStage.getBoundingClientRect();
        var x = (e.clientX - stageRect.left) / planoScale - dragOffX;
        var y = (e.clientY - stageRect.top) / planoScale - dragOffY;
        var maxX = (planoContentW > 0 ? planoContentW : canvas.clientWidth) - dragEl.offsetWidth;
        var maxY = (planoContentH > 0 ? planoContentH : canvas.clientHeight) - dragEl.offsetHeight;
        x = Math.max(0, Math.min(x, Math.max(0, maxX)));
        y = Math.max(0, Math.min(y, Math.max(0, maxY)));
        x = Math.round(x / 20) * 20;
        y = Math.round(y / 20) * 20;
        dragEl.style.left = x + 'px';
        dragEl.style.top = y + 'px';
        var id = parseInt(dragEl.dataset.id);
        var mesa = mesas.find(function(m) { return m.id === id; });
        if (mesa) { mesa.pos_x = x; mesa.pos_y = y; }
    });

    document.addEventListener('mouseup', function() {
        if (dragging && dragEl) { dragEl.style.cursor = ''; }
        dragging = false;
        dragEl = null;
    });

    canvas.addEventListener('touchstart', function(e) {
        var el = e.target.closest('.mesa-shape');
        if (!el) { deseleccionar(); return; }
        var id = parseInt(el.dataset.id);
        seleccionarMesa(id);
        var touch = e.touches[0];
        var stageRect = planoStage.getBoundingClientRect();
        dragOffX = (touch.clientX - stageRect.left) / planoScale - parseFloat(el.style.left || 0);
        dragOffY = (touch.clientY - stageRect.top) / planoScale - parseFloat(el.style.top || 0);
        dragging = true;
        dragEl = el;
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchmove', function(e) {
        if (!dragging || !dragEl) return;
        var touch = e.touches[0];
        var stageRect = planoStage.getBoundingClientRect();
        var x = (touch.clientX - stageRect.left) / planoScale - dragOffX;
        var y = (touch.clientY - stageRect.top) / planoScale - dragOffY;
        var maxX = (planoContentW > 0 ? planoContentW : canvas.clientWidth) - dragEl.offsetWidth;
        var maxY = (planoContentH > 0 ? planoContentH : canvas.clientHeight) - dragEl.offsetHeight;
        x = Math.max(0, Math.min(x, Math.max(0, maxX)));
        y = Math.max(0, Math.min(y, Math.max(0, maxY)));
        x = Math.round(x / 20) * 20;
        y = Math.round(y / 20) * 20;
        dragEl.style.left = x + 'px';
        dragEl.style.top = y + 'px';
        var id = parseInt(dragEl.dataset.id);
        var mesa = mesas.find(function(m) { return m.id === id; });
        if (mesa) { mesa.pos_x = x; mesa.pos_y = y; }
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchend', function() {
        dragging = false;
        dragEl = null;
    });

    canvas.addEventListener('dblclick', function(e) {
        var el = e.target.closest('.mesa-shape');
        if (!el) return;
        abrirModalMesa(parseInt(el.dataset.id));
    });
}

var modalMesaId = null;
var modalMesaColor = null;

function seleccionarColorModal(color) {
    modalMesaColor = color || null;
    document.querySelectorAll('#color-palette-modal .color-swatch').forEach(function(sw) {
        sw.classList.toggle('active', (sw.dataset.color || '') === (modalMesaColor || ''));
    });
}

function abrirModalMesa(id) {
    var mesa = mesas.find(function(m) { return m.id === id; });
    if (!mesa) return;
    modalMesaId = id;
    document.getElementById('mm-numero').value = mesa.numero || '';
    document.getElementById('mm-zona').value = mesa.zona_id || '';
    document.getElementById('mm-disponible').checked = !!mesa.disponible;
    seleccionarColorModal(mesa.color || '');
    document.getElementById('modal-mesa-numero-titulo').textContent = mesa.numero ? '— Mesa ' + mesa.numero : '';
    document.getElementById('modal-mesa-error').style.display = 'none';
    document.getElementById('modal-mesa-overlay').style.display = 'block';
    document.getElementById('modal-mesa').style.display = 'block';
    document.getElementById('mm-numero').focus();
}

function cerrarModalMesa() {
    document.getElementById('modal-mesa-overlay').style.display = 'none';
    document.getElementById('modal-mesa').style.display = 'none';
    modalMesaId = null;
    modalMesaColor = null;
}

function guardarModalMesa() {
    var statusEl = document.getElementById('plano-status');
    var errEl = document.getElementById('modal-mesa-error');
    errEl.style.display = 'none';
    if (modalMesaId === null) return;

    var payload = {
        numero: document.getElementById('mm-numero').value.trim(),
        zona_id: document.getElementById('mm-zona').value || null,
        disponible: document.getElementById('mm-disponible').checked ? 1 : 0,
        color: modalMesaColor,
    };

    fetch('{{ route('admin.mesas.update', ':ID') }}'.replace(':ID', modalMesaId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var mesa = mesas.find(function(m) { return m.id === modalMesaId; });
            if (mesa) {
                mesa.numero = res.mesa.numero;
                mesa.zona_id = res.mesa.zona_id;
                mesa.disponible = res.mesa.disponible;
                mesa.color = res.mesa.color;
                renderizarMesas();
                var el = document.querySelector('.mesa-shape[data-id="' + modalMesaId + '"]');
                if (el) {
                    el.dataset.numero = mesa.numero;
                    var label = el.querySelector('.mesa-label');
                    if (label) label.textContent = mesa.numero;
                }
            }
            cerrarModalMesa();
            statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Mesa actualizada exitosamente';
            statusEl.className = 'plano-status success';
            setTimeout(function() { statusEl.className = 'plano-status'; }, 4000);
        } else {
            errEl.textContent = res.message || 'Error al actualizar la mesa';
            errEl.style.display = 'block';
        }
    })
    .catch(function() {
        errEl.textContent = 'Error de conexión';
        errEl.style.display = 'block';
    });
}

function eliminarMesaModal() {
    if (modalMesaId === null) return;

    var id = modalMesaId;
    var mesa = mesas.find(function(m) { return m.id === id; });
    var numero = mesa ? mesa.numero : '';
    if (!confirm('¿Eliminar la Mesa ' + numero + '? Esta acción no se puede deshacer.')) return;

    var statusEl = document.getElementById('plano-status');
    var errEl = document.getElementById('modal-mesa-error');
    errEl.style.display = 'none';

    fetch('{{ route('admin.mesas.destroy', ':ID') }}'.replace(':ID', id), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
    })
    .then(function(r) {
        return r.json().then(function(body) {
            return { ok: r.ok, body: body };
        });
    })
    .then(function(res) {
        if (!res.ok || !res.body.success) {
            errEl.textContent = res.body.message || 'No se pudo eliminar la mesa';
            errEl.style.display = 'block';
            return;
        }

        var el = document.querySelector('.mesa-shape[data-id="' + id + '"]');
        if (el) el.remove();
        mesas = mesas.filter(function(m) { return m.id !== id; });
        cerrarModalMesa();
        applyFitPlano();
        statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Mesa eliminada exitosamente';
        statusEl.className = 'plano-status success';
        setTimeout(function() { statusEl.className = 'plano-status'; }, 4000);
    })
    .catch(function() {
        errEl.textContent = 'Error de conexión';
        errEl.style.display = 'block';
    });
}

function guardarPlano() {
    var data = mesas.map(function(m) {
        return {
            id: m.id, pos_x: m.pos_x, pos_y: m.pos_y,
            forma: m.forma, ancho: m.ancho, alto: m.alto,
            color: m.color || null,
        };
    });

    return fetch('{{ route('admin.mesas.guardar-plano') }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mesas: data })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) {
            throw new Error(res.message || 'Error al guardar el plano');
        }
        return true;
    });
}

renderizarMesas();
applyFitPlano();

var fitPlanoTimer = null;
window.addEventListener('resize', function() {
    clearTimeout(fitPlanoTimer);
    fitPlanoTimer = setTimeout(applyFitPlano, 150);
});
</script>

<script>
var zonaTabDragSrc = null;
function zonaTabClick(e, tab) {
    if (tab.classList.contains('editing') || tab.querySelector('input')) return false;
    if (e.target.closest('.zona-tab-handle')) return true;
    if (tab.classList.contains('active')) {
        editarNombreZona(tab);
        return false;
    }
    return true;
}

function guardarPlanos() {
    var statusEl = document.getElementById('plano-status');
    statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
    statusEl.className = 'plano-status saving';

    var tabs = Array.from(document.querySelectorAll('#zona-tab-links .zona-tab'));
    var zonas = tabs.map(function(tab, i) {
        var zona = { id: parseInt(tab.dataset.zonaId), orden: i + 1 };
        if (tab.classList.contains('marcada-eliminar')) {
            zona.nombre = '';
        } else if (tab.dataset.zonaEditada === '1') {
            zona.nombre = tab.dataset.originalNombre;
        }
        return zona;
    });

    ejecutarGuardado(statusEl, zonas);
}

function ejecutarGuardado(statusEl, zonas) {
    var pZonas = fetch('{{ route("admin.zonas.reordenar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ zonas: zonas })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) { throw new Error(res.message || 'Error al guardar zonas'); }
        return true;
    });

    Promise.all([guardarPlano(), pZonas])
    .then(function() {
        var eliminados = zonas.filter(function(z) {
            return z.nombre !== undefined && z.nombre.trim() === '';
        });
        eliminados.forEach(function(z) {
            var tab = document.querySelector('#zona-tab-links .zona-tab[data-zona-id="' + z.id + '"]');
            if (tab) tab.remove();
        });
        statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Guardado exitosamente';
        statusEl.className = 'plano-status success';
        setTimeout(function() { statusEl.className = 'plano-status'; }, 4000);
    })
    .catch(function(err) {
        statusEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + (err.message || 'Error al guardar');
        statusEl.className = 'plano-status error';
        setTimeout(function() { statusEl.className = 'plano-status'; }, 4000);
    });
}

function eliminarZonaTab(e, x) {
    e.stopPropagation();
    e.preventDefault();
    var tab = x.closest('.zona-tab');
    if (!tab) return;
    var nombre = tab.querySelector('.zona-tab-name') ? tab.querySelector('.zona-tab-name').textContent : tab.dataset.originalNombre || '';
    abrirModalEliminarZona(nombre, function(confirmar) {
        if (confirmar) {
            tab.classList.add('marcada-eliminar');
        }
    });
}

function editarNombreZona(tabEl) {
    var nombreActual = tabEl.querySelector('.zona-tab-name') ? tabEl.querySelector('.zona-tab-name').textContent : tabEl.textContent.trim();
    tabEl.setAttribute('draggable', 'false');
    tabEl.innerHTML = '<input type="text" class="zona-tab-input">';
    var input = tabEl.querySelector('input');
    input.value = nombreActual;
    input.focus();
    input.select();

    var render = function(texto) {
        tabEl.innerHTML = '<span class="zona-tab-handle" draggable="true"><i class="fa-solid fa-grip-vertical"></i></span><span class="zona-tab-name">' + texto + '</span><span class="zona-tab-close" onclick="eliminarZonaTab(event, this)" title="Eliminar zona">&times;</span>';
        tabEl.setAttribute('draggable', 'true');
        tabEl.classList.remove('editing');
    };

    var commit = function() {
        var nuevo = input.value.trim();
        if (nuevo === '') {
            render(nombreActual);
            delete tabEl.dataset.zonaEditada;
            delete tabEl.dataset.originalNombre;
        } else {
            render(nuevo);
            tabEl.dataset.originalNombre = nuevo;
            tabEl.dataset.zonaEditada = '1';
        }
    };
    tabEl.classList.add('editing');
    input.addEventListener('blur', commit);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = nombreActual; input.blur(); }
    });
}

function startZonaTabDrag(e) {
    if (!e.target.closest('.zona-tab-handle')) return;
    var tab = e.target.closest('.zona-tab');
    if (!tab) return;
    if (tab.classList.contains('editing') || tab.querySelector('input')) return;
    zonaTabDragSrc = tab;
    tab.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', tab.dataset.zonaId);
}

function initZonaTabDragDrop() {
    var container = document.getElementById('zona-tab-links');
    if (!container) return;

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!zonaTabDragSrc) return;
        e.dataTransfer.dropEffect = 'move';
        var target = e.target.closest('.zona-tab');
        if (!target || target === zonaTabDragSrc) return;
        var rect = target.getBoundingClientRect();
        var mid = rect.left + rect.width / 2;
        if (e.clientX < mid) {
            container.insertBefore(zonaTabDragSrc, target);
        } else {
            container.insertBefore(zonaTabDragSrc, target.nextSibling);
        }
    });

    container.addEventListener('drop', function(e) {
        e.preventDefault();
        finishZonaTabDrag();
    });

    container.addEventListener('dragend', finishZonaTabDrag);
}

function finishZonaTabDrag() {
    if (zonaTabDragSrc) {
        zonaTabDragSrc.classList.remove('dragging');
    }
    zonaTabDragSrc = null;
}

function editarZona(id, nombre) {
    fetch('/admin/zonas/' + id, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) {
            alert(res.message || 'Error al actualizar zona');
        }
    })
    .catch(function() { alert('Error de conexión'); });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initZonaTabDragDrop);
} else {
    initZonaTabDragDrop();
}
</script>

<div id="modal-zona-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;backdrop-filter:blur(2px)"></div>
<div id="modal-zona" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);padding:24px;z-index:201;width:340px;max-width:90vw">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Nueva Zona</div>
    <input type="text" id="modal-zona-nombre" class="form-input" placeholder="Nombre de la zona" maxlength="50" style="width:100%;margin-bottom:14px">
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="btn-link" onclick="cerrarModalZona()">Cancelar</button>
        <button type="button" class="btn btn-gold btn-sm" onclick="crearZona()">Crear</button>
    </div>
</div>

<div id="modal-eliminar-zona-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;backdrop-filter:blur(2px)"></div>
<div id="modal-eliminar-zona" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);padding:24px;z-index:201;width:360px;max-width:90vw">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Eliminar zona</div>
    <div id="modal-eliminar-zona-msg" style="font-size:13px;color:var(--text-2);margin-bottom:18px;line-height:1.5"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="btn-link" onclick="cerrarModalEliminarZona()">Cancelar</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminarZona()">Eliminar</button>
    </div>
</div>

<div id="modal-mesa-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;backdrop-filter:blur(2px)"></div>
<div id="modal-mesa" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);padding:24px;z-index:201;width:380px;max-width:90vw">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Editar Mesa <span id="modal-mesa-numero-titulo"></span></div>
    <div id="modal-mesa-error" style="display:none;font-size:13px;color:var(--red-text);background:var(--red-bg);border:1px solid var(--red);border-radius:var(--radius);padding:8px 12px;margin-bottom:14px"></div>
    <div style="margin-bottom:14px">
        <label class="form-label" for="mm-numero">Número</label>
        <input type="text" id="mm-numero" class="form-input" maxlength="10">
    </div>
    <div style="margin-bottom:14px">
        <label class="form-label" for="mm-zona">Zona</label>
        <select id="mm-zona" class="form-select" style="width:100%">
            <option value="">Sin zona</option>
            @foreach ($zonas as $z)
                <option value="{{ $z->id }}">{{ $z->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div style="margin-bottom:18px">
        <label class="form-checkbox" style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" id="mm-disponible" value="1">
            Disponible
        </label>
    </div>
    <div style="margin-bottom:18px">
        <label class="form-label">Color</label>
        <div class="color-palette" id="color-palette-modal">
            <button type="button" class="color-swatch clear-btn" data-color="" onclick="seleccionarColorModal('')" title="Color por defecto">✕</button>
            <button type="button" class="color-swatch" data-color="#2d8f5e" style="background:#2d8f5e" onclick="seleccionarColorModal('#2d8f5e')" title="Verde"></button>
            <button type="button" class="color-swatch" data-color="#3a7bbd" style="background:#3a7bbd" onclick="seleccionarColorModal('#3a7bbd')" title="Azul"></button>
            <button type="button" class="color-swatch" data-color="#7c4dff" style="background:#7c4dff" onclick="seleccionarColorModal('#7c4dff')" title="Púrpura"></button>
            <button type="button" class="color-swatch" data-color="#e67e22" style="background:#e67e22" onclick="seleccionarColorModal('#e67e22')" title="Naranja"></button>
            <button type="button" class="color-swatch" data-color="#e74c8b" style="background:#e74c8b" onclick="seleccionarColorModal('#e74c8b')" title="Rosa"></button>
            <button type="button" class="color-swatch" data-color="#1abc9c" style="background:#1abc9c" onclick="seleccionarColorModal('#1abc9c')" title="Turquesa"></button>
            <button type="button" class="color-swatch" data-color="#f1c40f" style="background:#f1c40f" onclick="seleccionarColorModal('#f1c40f')" title="Amarillo"></button>
            <button type="button" class="color-swatch" data-color="#95a5a6" style="background:#95a5a6" onclick="seleccionarColorModal('#95a5a6')" title="Gris"></button>
            <button type="button" class="color-swatch" data-color="#e74c3c" style="background:#e74c3c" onclick="seleccionarColorModal('#e74c3c')" title="Rojo"></button>
            <button type="button" class="color-swatch" data-color="#1a1a2e" style="background:#1a1a2e" onclick="seleccionarColorModal('#1a1a2e')" title="Negro"></button>
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <button type="button" class="btn btn-danger btn-sm" style="margin-right:auto" onclick="eliminarMesaModal()"><i class="fa-solid fa-trash"></i> Eliminar</button>
        <button type="button" class="btn-link" onclick="cerrarModalMesa()">Cancelar</button>
        <button type="button" class="btn btn-gold btn-sm" onclick="guardarModalMesa()">Guardar</button>
    </div>
</div>

<script>
function abrirModalZona() {
    document.getElementById('modal-zona-overlay').style.display = 'block';
    document.getElementById('modal-zona').style.display = 'block';
    document.getElementById('modal-zona-nombre').value = '';
    document.getElementById('modal-zona-nombre').focus();
}
function cerrarModalZona() {
    document.getElementById('modal-zona-overlay').style.display = 'none';
    document.getElementById('modal-zona').style.display = 'none';
}
function crearZona() {
    var nombre = document.getElementById('modal-zona-nombre').value.trim();
    if (!nombre) return;
    fetch('{{ route("admin.zonas.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            cerrarModalZona();
            var container = document.getElementById('zona-tab-links');
            if (container && res.zona && res.zona.id) {
                var nuevaZona = document.createElement('a');
                nuevaZona.className = 'zona-tab';
                nuevaZona.href = '{{ route("admin.mesas.index", ["tab" => "plano"]) }}?zona=' + res.zona.id;
                nuevaZona.dataset.zonaId = res.zona.id;
                nuevaZona.setAttribute('ondragstart', 'startZonaTabDrag(event)');
                nuevaZona.setAttribute('onclick', 'return zonaTabClick(event, this)');
                nuevaZona.innerHTML = '<span class="zona-tab-handle" draggable="true"><i class="fa-solid fa-grip-vertical"></i></span><span class="zona-tab-name">' + res.zona.nombre + '</span><span class="zona-tab-close" onclick="eliminarZonaTab(event, this)" title="Eliminar zona">&times;</span>';
                container.appendChild(nuevaZona);
            } else {
                window.location.reload();
            }
        } else {
            alert(res.message || 'Error al crear zona');
        }
    })
    .catch(function() { alert('Error de conexión'); });
}
document.getElementById('modal-zona-nombre').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); crearZona(); }
    if (e.key === 'Escape') cerrarModalZona();
});
document.getElementById('modal-zona-overlay').addEventListener('click', cerrarModalZona);
document.getElementById('modal-mesa-overlay').addEventListener('click', cerrarModalMesa);
document.getElementById('modal-mesa').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalMesa();
});
</script>
<script>
var zonaEliminarCallback = null;

function abrirModalEliminarZona(nombre, callback) {
    zonaEliminarCallback = callback;
    document.getElementById('modal-eliminar-zona-msg').textContent = '¿Deseas eliminar la zona «' + nombre + '»? Sus mesas pasarán a «Sin zona».';
    document.getElementById('modal-eliminar-zona-overlay').style.display = 'block';
    document.getElementById('modal-eliminar-zona').style.display = 'block';
}
function cerrarModalEliminarZona(confirmado) {
    document.getElementById('modal-eliminar-zona-overlay').style.display = 'none';
    document.getElementById('modal-eliminar-zona').style.display = 'none';
    var cb = zonaEliminarCallback;
    zonaEliminarCallback = null;
    if (cb) cb(!!confirmado);
}
function confirmarEliminarZona() {
    cerrarModalEliminarZona(true);
}
document.getElementById('modal-eliminar-zona-overlay').addEventListener('click', function() {
    cerrarModalEliminarZona(false);
});
</script>
@endsection
