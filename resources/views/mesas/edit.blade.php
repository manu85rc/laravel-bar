@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Mesa</div>
        <div class="page-sub">Mesa {{ $mesa->numero }}</div>
    </div>
    <a href="{{ route('admin.mesas.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if (session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mesas.update', $mesa->id) }}">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="numero">Número de Mesa</label>
                        <input type="text" name="numero" id="numero" class="form-input" value="{{ old('numero', $mesa->numero) }}" required>
                        @if ($errors->has('numero'))<span class="form-error">{{ $errors->first('numero') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="zona_id">Zona</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <select name="zona_id" id="zona_id" class="form-select" style="flex:1">
                                <option value="">Sin zona</option>
                                @foreach ($zonas as $z)
                                    <option value="{{ $z->id }}" {{ old('zona_id', $mesa->zona_id) == $z->id ? 'selected' : '' }}>{{ $z->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="abrirModalZona()"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        @if ($errors->has('zona_id'))<span class="form-error">{{ $errors->first('zona_id') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <div class="form-checkbox">
                            <input type="hidden" name="disponible" value="0">
                            <input type="checkbox" name="disponible" value="1" id="disponible" {{ old('disponible', $mesa->disponible) ? 'checked' : '' }}>
                            <label for="disponible">Disponible</label>
                        </div>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Color</label>
                        <input type="hidden" name="color" id="color" value="{{ old('color', $mesa->color) }}">
                        <div class="color-palette" id="color-palette-edit">
                            <button type="button" class="color-swatch clear-btn" data-color="" onclick="seleccionarColorEdicion('')" title="Color por defecto">✕</button>
                            <button type="button" class="color-swatch" data-color="#2d8f5e" style="background:#2d8f5e" onclick="seleccionarColorEdicion('#2d8f5e')" title="Verde"></button>
                            <button type="button" class="color-swatch" data-color="#3a7bbd" style="background:#3a7bbd" onclick="seleccionarColorEdicion('#3a7bbd')" title="Azul"></button>
                            <button type="button" class="color-swatch" data-color="#7c4dff" style="background:#7c4dff" onclick="seleccionarColorEdicion('#7c4dff')" title="Púrpura"></button>
                            <button type="button" class="color-swatch" data-color="#e67e22" style="background:#e67e22" onclick="seleccionarColorEdicion('#e67e22')" title="Naranja"></button>
                            <button type="button" class="color-swatch" data-color="#e74c8b" style="background:#e74c8b" onclick="seleccionarColorEdicion('#e74c8b')" title="Rosa"></button>
                            <button type="button" class="color-swatch" data-color="#1abc9c" style="background:#1abc9c" onclick="seleccionarColorEdicion('#1abc9c')" title="Turquesa"></button>
                            <button type="button" class="color-swatch" data-color="#f1c40f" style="background:#f1c40f" onclick="seleccionarColorEdicion('#f1c40f')" title="Amarillo"></button>
                            <button type="button" class="color-swatch" data-color="#95a5a6" style="background:#95a5a6" onclick="seleccionarColorEdicion('#95a5a6')" title="Gris"></button>
                            <button type="button" class="color-swatch" data-color="#e74c3c" style="background:#e74c3c" onclick="seleccionarColorEdicion('#e74c3c')" title="Rojo"></button>
                            <button type="button" class="color-swatch" data-color="#1a1a2e" style="background:#1a1a2e" onclick="seleccionarColorEdicion('#1a1a2e')" title="Negro"></button>
                        </div>
                        @if ($errors->has('color'))<span class="form-error">{{ $errors->first('color') }}</span>@endif
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.mesas.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Mesa</button>
                </div>
            </form>

            <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border-dim)">
                <div class="page-sub" style="margin-bottom:10px">Eliminar esta mesa de forma permanente.</div>
                <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}" onsubmit="return confirm('¿Eliminar la Mesa {{ $mesa->numero }}? Esta acción no se puede deshacer.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Eliminar Mesa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<div id="modal-zona-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;backdrop-filter:blur(2px)"></div>
<div id="modal-zona" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--bg-3);border:1px solid var(--border-dim);border-radius:var(--radius-lg);padding:24px;z-index:201;width:340px;max-width:90vw">
    <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:14px">Nueva Zona</div>
    <input type="text" id="modal-zona-nombre" class="form-input" placeholder="Nombre de la zona" maxlength="50" style="width:100%;margin-bottom:14px">
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="btn-link" onclick="cerrarModalZona()">Cancelar</button>
        <button type="button" class="btn btn-gold btn-sm" onclick="crearZona()">Crear</button>
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
            var sel = document.getElementById('zona_id');
            var opt = document.createElement('option');
            opt.value = res.zona.id;
            opt.textContent = res.zona.nombre;
            sel.appendChild(opt);
            sel.value = res.zona.id;
            cerrarModalZona();
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
</script>

<script>
function seleccionarColorEdicion(color) {
    document.getElementById('color').value = color || '';
    document.querySelectorAll('#color-palette-edit .color-swatch').forEach(function(sw) {
        sw.classList.toggle('active', (sw.dataset.color || '') === (color || ''));
    });
}

(function() {
    var colorActual = document.getElementById('color').value || '';
    document.querySelectorAll('#color-palette-edit .color-swatch').forEach(function(sw) {
        sw.classList.toggle('active', (sw.dataset.color || '') === colorActual);
    });
})();
</script>
