@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Plano de Mesas</div>
        <div class="page-sub">{{ $pisoActual ?? 'Todas las mesas' }} · Organiza las mesas arrastrándolas sobre el plano</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.mesas.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        <button class="btn btn-gold" onclick="guardarPlano()"><i class="fa-solid fa-floppy-disk"></i> Guardar Plano</button>
    </div>
</div>

@if (!empty($pisos))
<div class="piso-tabs">
    @foreach ($pisos as $p)
        <a href="{{ route('admin.mesas.plano', ['piso' => $p]) }}" class="piso-tab {{ $p === $pisoActual ? 'active' : '' }}">{{ $p }}</a>
    @endforeach
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
        <span class="toolbar-label" style="opacity:.5">Mesa <span id="sel-num">—</span></span>
    </div>
</div>

<div class="plano-wrapper">
    <div class="plano-canvas" id="plano-canvas">
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
            <div class="mesa-shape {{ $estado }} {{ $mesa->forma }}"
                 data-id="{{ $mesa->id }}"
                 data-numero="{{ $mesa->numero }}"
                 data-capacidad="{{ $mesa->capacidad }}"
                 data-estado="{{ $estado }}"
                 data-label="{{ $label }}"
                 style="left:{{ $mesa->pos_x }}px; top:{{ $mesa->pos_y }}px; width:{{ $mesa->ancho }}px; height:{{ $mesa->alto }}px;">
                <div class="mesa-pax"><i class="fa-solid fa-user"></i> {{ $mesa->capacidad }}</div>
                <div class="mesa-label">{{ $mesa->numero }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="plano-status" id="plano-status"></div>

@if (session('success'))
    <div class="alert alert-success" style="margin-top:16px"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<script>
var mesas = @json($mesasData);

var selectedId = null;
var dragging = false;
var dragEl = null;
var dragOffX = 0, dragOffY = 0;

var canvas = document.getElementById('plano-canvas');

// Auto-arrange if all at 0,0
var allAtZero = mesas.every(function(m) { return m.pos_x === 0 && m.pos_y === 0; });
if (allAtZero) {
    var cols = 4;
    var spacingX = 140;
    var spacingY = 120;
    var startX = 30, startY = 30;
    mesas.forEach(function(m, i) {
        var col = i % cols;
        var row = Math.floor(i / cols);
        m.pos_x = startX + col * spacingX;
        m.pos_y = startY + row * spacingY;
    });
}

function renderizarMesas() {
    var shapes = canvas.querySelectorAll('.mesa-shape');
    var map = {};
    shapes.forEach(function(el) {
        map[el.dataset.id] = el;
    });
    mesas.forEach(function(m) {
        var el = map[m.id];
        if (el) {
            el.style.left = m.pos_x + 'px';
            el.style.top = m.pos_y + 'px';
            el.style.width = m.ancho + 'px';
            el.style.height = m.alto + 'px';
            el.className = 'mesa-shape ' + el.dataset.estado + ' ' + m.forma;
            if (selectedId == m.id) el.classList.add('selected');
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
        var el = canvas.querySelector('.mesa-shape[data-id="' + id + '"]');
        if (el) el.classList.add('selected');
    }
}

function deseleccionar() {
    selectedId = null;
    document.getElementById('sel-num').textContent = '—';
    canvas.querySelectorAll('.mesa-shape').forEach(function(el) { el.classList.remove('selected'); });
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

// Drag & Drop
canvas.addEventListener('mousedown', function(e) {
    var el = e.target.closest('.mesa-shape');
    if (!el) { deseleccionar(); return; }
    var id = parseInt(el.dataset.id);
    seleccionarMesa(id);
    var rect = el.getBoundingClientRect();
    dragOffX = e.clientX - rect.left;
    dragOffY = e.clientY - rect.top;
    dragging = true;
    dragEl = el;
    el.style.cursor = 'grabbing';
    e.preventDefault();
});

document.addEventListener('mousemove', function(e) {
    if (!dragging || !dragEl) return;
    var canvasRect = canvas.getBoundingClientRect();
    var x = e.clientX - canvasRect.left - dragOffX;
    var y = e.clientY - canvasRect.top - dragOffY;
    x = Math.max(0, Math.min(x, canvasRect.width - dragEl.offsetWidth));
    y = Math.max(0, Math.min(y, canvasRect.height - dragEl.offsetHeight));
    // Snap to grid (20px)
    x = Math.round(x / 20) * 20;
    y = Math.round(y / 20) * 20;
    dragEl.style.left = x + 'px';
    dragEl.style.top = y + 'px';
    var id = parseInt(dragEl.dataset.id);
    var mesa = mesas.find(function(m) { return m.id === id; });
    if (mesa) {
        mesa.pos_x = x;
        mesa.pos_y = y;
    }
});

document.addEventListener('mouseup', function() {
    if (dragging && dragEl) {
        dragEl.style.cursor = '';
    }
    dragging = false;
    dragEl = null;
});

// Touch support
canvas.addEventListener('touchstart', function(e) {
    var el = e.target.closest('.mesa-shape');
    if (!el) { deseleccionar(); return; }
    var id = parseInt(el.dataset.id);
    seleccionarMesa(id);
    var touch = e.touches[0];
    var rect = el.getBoundingClientRect();
    dragOffX = touch.clientX - rect.left;
    dragOffY = touch.clientY - rect.top;
    dragging = true;
    dragEl = el;
    e.preventDefault();
}, { passive: false });

document.addEventListener('touchmove', function(e) {
    if (!dragging || !dragEl) return;
    var touch = e.touches[0];
    var canvasRect = canvas.getBoundingClientRect();
    var x = touch.clientX - canvasRect.left - dragOffX;
    var y = touch.clientY - canvasRect.top - dragOffY;
    x = Math.max(0, Math.min(x, canvasRect.width - dragEl.offsetWidth));
    y = Math.max(0, Math.min(y, canvasRect.height - dragEl.offsetHeight));
    x = Math.round(x / 20) * 20;
    y = Math.round(y / 20) * 20;
    dragEl.style.left = x + 'px';
    dragEl.style.top = y + 'px';
    var id = parseInt(dragEl.dataset.id);
    var mesa = mesas.find(function(m) { return m.id === id; });
    if (mesa) {
        mesa.pos_x = x;
        mesa.pos_y = y;
    }
    e.preventDefault();
}, { passive: false });

document.addEventListener('touchend', function() {
    dragging = false;
    dragEl = null;
});

// Guardar plano
function guardarPlano() {
    var statusEl = document.getElementById('plano-status');
    statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
    statusEl.className = 'plano-status saving';

    var data = mesas.map(function(m) {
        return {
            id: m.id,
            pos_x: m.pos_x,
            pos_y: m.pos_y,
            forma: m.forma,
            ancho: m.ancho,
            alto: m.alto,
        };
    });

    fetch('{{ route('admin.mesas.guardar-plano') }}', {
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
        if (res.success) {
            statusEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + res.message;
            statusEl.className = 'plano-status success';
        } else {
            statusEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + (res.message || 'Error al guardar');
            statusEl.className = 'plano-status error';
        }
        setTimeout(function() {
            statusEl.className = 'plano-status';
        }, 4000);
    })
    .catch(function() {
        statusEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error de conexión';
        statusEl.className = 'plano-status error';
    });
}

// Initial render
renderizarMesas();
</script>
@endsection
