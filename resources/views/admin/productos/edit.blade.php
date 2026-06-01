@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Producto</div>
        <div class="page-sub">{{ $producto->nombre }}</div>
    </div>
    <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-input" value="{{ old('nombre', $producto->nombre) }}" required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-textarea">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="precio">Precio</label>
                        <input type="number" name="precio" id="precio" step="0.01" min="0" class="form-input" value="{{ old('precio', $producto->precio) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="categoria_id">Categoría</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">Ninguna</option>
                            @if($categorias && $categorias->count() > 0)
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="comida" {{ old('tipo', $producto->tipo) == 'comida' ? 'selected' : '' }}>Comida</option>
                            <option value="bebida" {{ old('tipo', $producto->tipo) == 'bebida' ? 'selected' : '' }}>Bebida</option>
                        </select>
                        <span class="form-hint">Define si es para cocina o barra</span>
                    </div>

                    <div class="form-group full" x-data="{ preview: null }">
                        <label class="form-label">Foto</label>
                        @if ($producto->foto_path)
                            <img src="{{ $producto->fotoUrl() }}" alt="" class="img-preview" style="margin-top:0;margin-bottom:8px">
                            <div class="form-checkbox" style="margin-bottom:8px">
                                <input type="checkbox" name="eliminar_foto" id="eliminar_foto" value="1">
                                <label for="eliminar_foto" style="color:var(--red-text)">Eliminar foto actual</label>
                            </div>
                        @endif
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
                            <input type="checkbox" name="disponible" value="1" id="disponible" {{ old('disponible', $producto->disponible) ? 'checked' : '' }}>
                            <label for="disponible">Disponible</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.productos.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
