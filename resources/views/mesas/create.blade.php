@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Nueva Mesa</div>
        <div class="page-sub">Agrega una mesa al salón</div>
    </div>
    <a href="{{ route('admin.mesas.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mesas.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="numero">Número de Mesa</label>
                        <input type="text" name="numero" id="numero" class="form-input" value="{{ old('numero') }}" required>
                        @if ($errors->has('numero'))<span class="form-error">{{ $errors->first('numero') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="zona_id">Zona</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <select name="zona_id" id="zona_id" class="form-select" style="flex:1">
                                <option value="">Sin zona</option>
                                @foreach ($zonas as $z)
                                    <option value="{{ $z->id }}" {{ old('zona_id') == $z->id ? 'selected' : '' }}>{{ $z->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="abrirModalZona()"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        @if ($errors->has('zona_id'))<span class="form-error">{{ $errors->first('zona_id') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <div class="form-checkbox">
                            <input type="hidden" name="disponible" value="0">
                            <input type="checkbox" name="disponible" value="1" id="disponible" {{ old('disponible', true) ? 'checked' : '' }}>
                            <label for="disponible">Disponible</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.mesas.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Crear Mesa</button>
                </div>
            </form>
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
