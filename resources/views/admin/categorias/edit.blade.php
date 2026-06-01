@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Categoría</div>
        <div class="page-sub">{{ $categoria->name }}</div>
    </div>
    <a href="{{ route('admin.categorias.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $categoria->name) }}" required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="parent_id">Categoría Padre</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">Ninguna (categoría principal)</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $categoria->parent_id) == $cat->id ? 'selected' : '' }} @if ($cat->id === $categoria->id) disabled @endif>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="description">Descripción</label>
                        <textarea name="description" id="description" rows="3" class="form-textarea">{{ old('description', $categoria->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="orden">Orden</label>
                        <input type="number" name="orden" id="orden" class="form-input" value="{{ old('orden', $categoria->orden) }}" min="0">
                        <span class="form-hint">Posición en la carta (0 = primero).</span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.categorias.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
