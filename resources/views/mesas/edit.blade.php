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

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mesas.update', $mesa->id) }}">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="numero">Número de Mesa</label>
                        <input type="text" name="numero" id="numero" class="form-input" value="{{ old('numero', $mesa->numero) }}" required>
                        @if ($errors->has('numero'))<span class="form-error">{{ $errors->first('numero') }}</span>@endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="capacidad">Capacidad</label>
                        <input type="number" name="capacidad" id="capacidad" class="form-input" value="{{ old('capacidad', $mesa->capacidad) }}" required min="1">
                        @if ($errors->has('capacidad'))<span class="form-error">{{ $errors->first('capacidad') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="ubicacion">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion" class="form-input" value="{{ old('ubicacion', $mesa->ubicacion) }}" placeholder="Ej: Terraza, Salón principal, Barra…">
                        @if ($errors->has('ubicacion'))<span class="form-error">{{ $errors->first('ubicacion') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="piso">Piso / Nivel</label>
                        <input type="text" name="piso" id="piso" class="form-input" value="{{ old('piso', $mesa->piso) }}" placeholder="Ej: Planta Baja, Terraza…">
                        @if ($errors->has('piso'))<span class="form-error">{{ $errors->first('piso') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <div class="form-checkbox">
                            <input type="hidden" name="disponible" value="0">
                            <input type="checkbox" name="disponible" value="1" id="disponible" {{ old('disponible', $mesa->disponible) ? 'checked' : '' }}>
                            <label for="disponible">Disponible</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.mesas.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
