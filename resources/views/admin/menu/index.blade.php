@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Gestión del Menú</div>
        <div class="page-sub">{{ $categorias->count() }} categorías · {{ $totalProductos }} productos</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.categorias.create') }}" class="btn btn-ghost"><i class="fa-solid fa-tags"></i> Nueva Categoría</a>
        <a href="{{ route('admin.productos.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo Producto</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if ($categorias->isEmpty())
    <div class="empty-state">No hay categorías. ¡Crea una nueva!</div>
@else
    @foreach ($categorias as $categoria)
    <div class="card" style="margin-bottom:16px">
        <div class="card-section" style="background:var(--bg-4)">
            <div class="page-section">
                <div>
                    <div class="card-section-title">{{ $categoria->name }}</div>
                    @if ($categoria->description)
                        <div class="card-section-sub">{{ $categoria->description }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <a href="{{ route('admin.productos.create', ['categoria_id' => $categoria->id]) }}" class="btn btn-ghost btn-sm" title="Nuevo producto en {{ $categoria->name }}"><i class="fa-solid fa-plus"></i> Producto</a>
                    <a href="{{ route('admin.categorias.edit', $categoria->id) }}" class="btn-link">Editar</a>
                    <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-link danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        @php $directProducts = $categoria->productos; @endphp
        @if ($directProducts->isNotEmpty())
            <div class="list-items">
                @foreach ($directProducts as $producto)
                    <div class="list-item">
                        @if ($producto->foto_path)
                            <img src="{{ $producto->fotoUrl() }}" alt="" style="width:32px;height:32px;border-radius:var(--radius);object-fit:cover">
                        @else
                            <div class="av-sm a1" style="width:32px;height:32px;border-radius:var(--radius)"><i class="fa-solid fa-bottle-droplet" style="font-size:11px"></i></div>
                        @endif
                        <div class="list-item-info">
                            <div class="list-item-title">{{ $producto->nombre }}</div>
                            @if ($producto->descripcion)
                                <div class="list-item-sub">{{ str($producto->descripcion)->limit(60) }}</div>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;font-size:13px;font-weight:600;color:var(--gold-light);font-family:'Playfair Display',serif">${{ number_format($producto->precio, 0) }}</div>
                        <span class="badge {{ $producto->disponible ? 'b-green' : 'b-red' }}" style="flex-shrink:0"><span class="bdot"></span>{{ $producto->disponible ? 'Disponible' : 'Agotado' }}</span>
                        <div class="list-item-actions">
                            <a href="{{ route('admin.productos.edit', $producto->id) }}">Editar</a>
                            <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-link danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($categoria->children->isNotEmpty())
            @foreach ($categoria->children as $subcategoria)
                @php $subProducts = $subcategoria->productos; @endphp
                <div class="card-section">
                    <div class="page-section" style="margin-bottom:0">
                        <div>
                            <div class="card-section-title" style="font-size:12px;color:var(--text-2)"><i class="fa-solid fa-folder-open" style="margin-right:6px"></i>{{ $subcategoria->name }}</div>
                            @if ($subcategoria->description)
                                <div class="card-section-sub">{{ $subcategoria->description }}</div>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            <a href="{{ route('admin.productos.create', ['categoria_id' => $subcategoria->id]) }}" class="btn-link">+ Producto</a>
                            <a href="{{ route('admin.categorias.edit', $subcategoria->id) }}" class="btn-link">Editar</a>
                            <form action="{{ route('admin.categorias.destroy', $subcategoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-link danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
                @if ($subProducts->isNotEmpty())
                    <div class="list-items">
                        @foreach ($subProducts as $producto)
                            <div class="list-item" style="padding-left:36px">
                                @if ($producto->foto_path)
                                    <img src="{{ $producto->fotoUrl() }}" alt="" style="width:28px;height:28px;border-radius:var(--radius);object-fit:cover">
                                @else
                                    <div class="av-sm a2" style="width:28px;height:28px;border-radius:var(--radius);font-size:9px"><i class="fa-solid fa-bottle-droplet"></i></div>
                                @endif
                                <div class="list-item-info">
                                    <div class="list-item-title">{{ $producto->nombre }}</div>
                                    @if ($producto->descripcion)
                                        <div class="list-item-sub">{{ str($producto->descripcion)->limit(60) }}</div>
                                    @endif
                                </div>
                                <div style="text-align:right;flex-shrink:0;font-size:13px;font-weight:600;color:var(--gold-light);font-family:'Playfair Display',serif">${{ number_format($producto->precio, 0) }}</div>
                                <span class="badge {{ $producto->disponible ? 'b-green' : 'b-red' }}" style="flex-shrink:0"><span class="bdot"></span>{{ $producto->disponible ? 'Disponible' : 'Agotado' }}</span>
                                <div class="list-item-actions">
                                    <a href="{{ route('admin.productos.edit', $producto->id) }}">Editar</a>
                                    <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-link danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card-section"><div class="form-hint" style="padding:4px 0">Sin productos en esta subcategoría</div></div>
                @endif
            @endforeach
        @endif
    </div>
    @endforeach
@endif
@endsection
