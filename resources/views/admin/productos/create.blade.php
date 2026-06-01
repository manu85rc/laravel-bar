@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Nuevo Producto</div>
        <div class="page-sub">Agrega un producto a la carta</div>
    </div>
    <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-input" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-textarea">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="precio">Precio</label>
                        <input type="number" name="precio" id="precio" step="0.01" min="0" class="form-input" value="{{ old('precio') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="categoria_id">Categoría</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">Ninguna</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $selectedCategoriaId ?? '') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="comida" {{ old('tipo') == 'comida' ? 'selected' : '' }}>Comida</option>
                            <option value="bebida" {{ old('tipo') == 'bebida' ? 'selected' : '' }}>Bebida</option>
                        </select>
                        <span class="form-hint">Define si es para cocina o barra</span>
                    </div>

                    <div class="form-group full" x-data="{ preview: null }">
                        <label class="form-label">Foto (opcional)</label>
                        <div class="file-input-wrap">
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" @change="preview = URL.createObjectURL($event.target.files[0])">
                            <span class="form-hint">JPG, PNG, GIF, SVG, WebP — máx. 5MB</span>
                        </div>
                        <template x-if="preview">
                            <img :src="preview" alt="Vista previa" class="img-preview">
                        </template>
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
                    <a href="{{ route('admin.productos.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Crear Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
