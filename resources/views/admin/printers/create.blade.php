@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Nueva Impresora</div>
        <div class="page-sub">Configura una impresora POS para imprimir pedidos</div>
    </div>
    <a href="{{ route('admin.printers.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.printers.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-input" value="{{ old('nombre') }}" placeholder="Ej: Cocina, Barra, Comandas..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo de impresión</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="cocina" {{ old('tipo') == 'cocina' ? 'selected' : '' }}>Cocina (solo comida)</option>
                            <option value="bebidas" {{ old('tipo') == 'bebidas' ? 'selected' : '' }}>Bebidas (solo bebidas)</option>
                            <option value="ambas" {{ old('tipo') == 'ambas' ? 'selected' : '' }}>Ambas (todo)</option>
                            <option value="factura" {{ old('tipo') == 'factura' ? 'selected' : '' }}>Factura (ticket completo)</option>
                        </select>
                        <span class="form-hint">Imprime solo comida, solo bebidas, todos, o ticket de factura</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="papel">Tamaño del papel</label>
                        <select name="papel" id="papel" class="form-select" required>
                            <option value="80mm" {{ old('papel', '80mm') == '80mm' ? 'selected' : '' }}>80 mm</option>
                            <option value="58mm" {{ old('papel') == '58mm' ? 'selected' : '' }}>58 mm</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ruta_dispositivo">Ruta del dispositivo</label>
                        <input type="text" name="ruta_dispositivo" id="ruta_dispositivo" class="form-input" value="{{ old('ruta_dispositivo', '/dev/usb/lp0') }}" required>
                        <span class="form-hint">Ruta USB del dispositivo, ej: <code>/dev/usb/lp0</code></span>
                    </div>

                    <div class="form-group full">
                        <div class="form-checkbox">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" id="activo" {{ old('activo', true) ? 'checked' : '' }}>
                            <label for="activo">Activa</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.printers.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Crear Impresora</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
