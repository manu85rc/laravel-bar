@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Gestión de Productos</div>
        <div class="page-sub">{{ $productos->total() }} productos</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.productos.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo Producto</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<form method="GET" class="filter-bar">
    <div class="filter-group">
        <label>Buscar</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del producto..." class="form-input">
    </div>
    <div class="filter-group">
        <label>Categoría</label>
        <select name="categoria_id" class="form-select">
            <option value="">Todas</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Disponible</label>
        <select name="disponible" class="form-select">
            <option value="">Todos</option>
            <option value="1" {{ request('disponible') === '1' ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ request('disponible') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>
    <div class="btn-row" style="margin-bottom:2px">
        <button type="submit" class="btn btn-gold">Filtrar</button>
        <a href="{{ route('admin.productos.index') }}" class="btn btn-ghost">Limpiar</a>
    </div>
</form>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Disponible</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>
                            @if ($producto->foto_path)
                                <img src="{{ $producto->fotoUrl() }}" alt="" class="av-sm" style="border-radius:var(--radius);object-fit:cover;width:32px;height:32px">
                            @else
                                <div class="av-sm a1" style="font-size:9px"><i class="fa-solid fa-bottle-droplet"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="ucell">
                                <div>
                                    <strong>{{ $producto->nombre }}</strong>
                                    @if ($producto->descripcion)
                                        <small>{{ str($producto->descripcion)->limit(60) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $producto->categoria ? $producto->categoria->name : '—' }}</td>
                        <td>
                            @if ($producto->tipo)
                                <span class="badge {{ $producto->tipo == 'comida' ? 'b-amber' : 'b-blue' }}">
                                    {{ $producto->tipo == 'comida' ? 'Comida' : 'Bebida' }}
                                </span>
                            @else
                                <span class="badge b-gray">—</span>
                            @endif
                        </td>
                        <td class="amount-cell">${{ number_format($producto->precio, 0) }}</td>
                        <td>
                            <span class="badge {{ $producto->disponible ? 'b-green' : 'b-red' }}">
                                <span class="bdot"></span>{{ $producto->disponible ? 'Disponible' : 'Agotado' }}
                            </span>
                        </td>
                        <td>
                            <div class="list-item-actions">
                                <a href="{{ route('admin.productos.edit', $producto->id) }}">Editar</a>
                                <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No se encontraron productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">
    {{ $productos->appends(request()->query())->links() }}
</div>
@endsection
