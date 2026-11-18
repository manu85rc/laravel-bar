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

<form method="GET" class="filter-bar">
    <div class="filter-group">
        <label>Buscar</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del producto..." class="form-input">
    </div>
    <div class="filter-group">
        <label>Tipo</label>
        <select name="tipo" class="form-select">
            <option value="">Todos</option>
            <option value="comida" {{ request('tipo') === 'comida' ? 'selected' : '' }}>Comida</option>
            <option value="bebida" {{ request('tipo') === 'bebida' ? 'selected' : '' }}>Bebida</option>
        </select>
    </div>
    <div class="btn-row" style="margin-bottom:2px">
        <button type="submit" class="btn btn-gold">Filtrar</button>
        <a href="{{ route('admin.menu.index') }}" class="btn btn-ghost">Limpiar</a>
    </div>
</form>

@if ($categorias->isEmpty() && $sinCategoria->isEmpty())
    <div class="empty-state">No hay categorías que coincidan con el filtro. ¡Crea una nueva!</div>
@else
    <div class="form-hint" style="margin-bottom:12px"><i class="fa-solid fa-grip-vertical" style="margin-right:6px"></i>Arrastra los productos con el grip para reordenarlos dentro de su categoría, o suéltalos sobre otra categoría/subcategoría para moverlos (y en "Sin categoría" para desasignarlos). También puedes reordenar las categorías y las subcategorías arrastrando su grip.</div>

    <div x-data="menuUnificado()">
        <template x-if="saving">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:var(--gold-glow);border-radius:var(--radius);border:1px solid var(--border)">
                <i class="fa-solid fa-spinner fa-spin" style="color:var(--gold-light)"></i>
                <span style="font-size:12px;color:var(--gold-light)">Guardando cambios...</span>
            </div>
        </template>
        <template x-if="savedMsg && !saving">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:var(--green-bg);border-radius:var(--radius);border:1px solid var(--green)">
                <i class="fa-solid fa-circle-check" style="color:var(--green-text)"></i>
                <span style="font-size:12px;color:var(--green-text)">Cambios guardados</span>
            </div>
        </template>

        @if ($categorias->isNotEmpty())
        <div id="categorias-sortable">
            @foreach ($categorias as $categoria)
            <div class="card sort-item"
                 style="margin-bottom:16px"
                 data-id="{{ $categoria->id }}"
                 data-categoria-id="{{ $categoria->id }}">
                <div class="card-section" style="background:var(--bg-4)">
                    <div class="page-section">
                        <div style="display:flex;gap:10px;align-items:center">
                            <div class="drag-handle drag-handle-cat"><i class="fa-solid fa-grip-vertical"></i></div>
                            <div>
                                <div class="card-section-title">{{ $categoria->name }}</div>
                                @if ($categoria->description)
                                    <div class="card-section-sub">{{ $categoria->description }}</div>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            <a href="{{ route('admin.productos.create', ['categoria_id' => $categoria->id]) }}" class="btn btn-ghost btn-sm" title="Nuevo producto en {{ $categoria->name }}"><i class="fa-solid fa-plus"></i> Producto</a>
                            <a href="{{ route('admin.categorias.edit', $categoria->id) }}" class="btn-link">Editar</a>
                            <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría y todas sus subcategorías?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-link danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>

                @php $directProducts = $categoria->productos; @endphp
                    <div class="list-items {{ $directProducts->isEmpty() ? 'is-empty' : '' }}" data-grupo="cat-{{ $categoria->id }}" data-categoria-id="{{ $categoria->id }}">
                        @foreach ($directProducts as $producto)
                            <div class="list-item"
                                 data-producto-id="{{ $producto->id }}"
                                 data-categoria-id="{{ $producto->categoria_id }}">
                                <div class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></div>
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
                                @if ($producto->tipo)
                                    <span class="badge {{ $producto->tipo == 'comida' ? 'b-amber' : 'b-blue' }}" style="flex-shrink:0">{{ $producto->tipo == 'comida' ? 'Comida' : 'Bebida' }}</span>
                                @endif
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

                @if ($categoria->children->isNotEmpty())
                    <div class="subcategorias-sortable" data-parent-id="{{ $categoria->id }}">
                    @foreach ($categoria->children as $subcategoria)
                        @php $subProducts = $subcategoria->productos; @endphp
                        <div class="subcategoria-sort-item" data-id="{{ $subcategoria->id }}">
                        <div class="card-section">
                            <div class="page-section" style="margin-bottom:0">
                                <div style="display:flex;gap:10px;align-items:center">
                                    <div class="drag-handle-sub"><i class="fa-solid fa-grip-vertical"></i></div>
                                    <div>
                                        <div class="card-section-title" style="font-size:12px;color:var(--text-2)"><i class="fa-solid fa-folder-open" style="margin-right:6px"></i>{{ $subcategoria->name }}</div>
                                        @if ($subcategoria->description)
                                            <div class="card-section-sub">{{ $subcategoria->description }}</div>
                                        @endif
                                    </div>
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
                        <div class="list-items {{ $subProducts->isEmpty() ? 'is-empty' : '' }}" data-grupo="sub-{{ $subcategoria->id }}" data-categoria-id="{{ $subcategoria->id }}">
                            @foreach ($subProducts as $producto)
                                    <div class="list-item" style="padding-left:36px"
                                         data-producto-id="{{ $producto->id }}"
                                         data-categoria-id="{{ $producto->categoria_id }}">
                                        <div class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></div>
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
                                        @if ($producto->tipo)
                                            <span class="badge {{ $producto->tipo == 'comida' ? 'b-amber' : 'b-blue' }}" style="flex-shrink:0">{{ $producto->tipo == 'comida' ? 'Comida' : 'Bebida' }}</span>
                                        @endif
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
                        </div>
                    @endforeach
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <div class="card"
             style="margin-top:16px"
             data-grupo-card="sin">
            <div class="card-section" style="background:var(--bg-4)">
                <div class="page-section">
                    <div>
                        <div class="card-section-title"><i class="fa-solid fa-folder-open" style="margin-right:6px"></i>Sin categoría</div>
                        <div class="card-section-sub">{{ $sinCategoria->count() }} {{ $sinCategoria->count() == 1 ? 'producto' : 'productos' }} sin asignar</div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <a href="{{ route('admin.productos.create') }}" class="btn btn-ghost btn-sm" title="Nuevo producto sin categoría"><i class="fa-solid fa-plus"></i> Producto</a>
                    </div>
                </div>
            </div>
            <div class="list-items {{ $sinCategoria->isEmpty() ? 'is-empty' : '' }}" data-grupo="sin" data-categoria-id="">
                @foreach ($sinCategoria as $producto)
                    <div class="list-item"
                         data-producto-id="{{ $producto->id }}"
                         data-categoria-id="{{ $producto->categoria_id }}">
                        <div class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></div>
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
                        @if ($producto->tipo)
                            <span class="badge {{ $producto->tipo == 'comida' ? 'b-amber' : 'b-blue' }}" style="flex-shrink:0">{{ $producto->tipo == 'comida' ? 'Comida' : 'Bebida' }}</span>
                        @endif
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
        </div>
    </div>

    <script>
        function menuUnificado() {
            return {
                saving: false,
                savedMsg: false,
                productSortables: [],
                init() {
                    this.$nextTick(() => {
                        this.initProductSortables();
                        this.initCategorySortable();
                        this.initSubcategorySortables();
                    });
                },
                initCategorySortable() {
                    const container = document.getElementById('categorias-sortable');
                    if (!container) return;
                    const self = this;
                    window.Sortable.create(container, {
                        handle: '.drag-handle-cat',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        filter: '.list-item, input, button, select, a, form',
                        onEnd() {
                            self.persistirCategorias();
                        },
                    });
                },
                initSubcategorySortables() {
                    const self = this;
                    document.querySelectorAll('.subcategorias-sortable').forEach(container => {
                        window.Sortable.create(container, {
                            handle: '.drag-handle-sub',
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'sortable-drag',
                            filter: '.list-item, input, button, select, a, form',
                            onEnd() {
                                self.persistirSubcategorias(container);
                            },
                        });
                    });
                },
                parseGrupo(grupo) {
                    if (grupo === 'sin') return { categoria_id: null };
                    const m = /^(?:cat|sub)-(\d+)$/.exec(grupo || '');
                    return m ? { categoria_id: parseInt(m[1], 10) } : null;
                },
                initProductSortables() {
                    const self = this;
                    document.querySelectorAll('.list-items[data-grupo]').forEach(container => {
                        const sortable = window.Sortable.create(container, {
                            group: { name: 'productos', pull: true, put: true },
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'sortable-drag',
                            filter: 'input, button, select, a, form',
                            onEnd(evt) {
                                if (evt.to && evt.to.dataset) {
                                    const info = self.parseGrupo(evt.to.dataset.grupo);
                                    if (info) {
                                        Array.from(evt.to.querySelectorAll(':scope > .list-item')).forEach(item => {
                                            item.dataset.categoriaId = info.categoria_id === null ? '' : String(info.categoria_id);
                                        });
                                    }
                                }
                                self.persistirProductos();
                            },
                        });
                        self.productSortables.push(sortable);
                    });
                },
                persistirCategorias() {
                    const orden = Array.from(document.querySelectorAll('#categorias-sortable > .sort-item'))
                        .map(el => parseInt(el.dataset.id, 10));
                    if (!orden.length) return;
                    this.enviarOrden(orden);
                },
                persistirSubcategorias(container) {
                    const orden = Array.from(container.querySelectorAll(':scope > .subcategoria-sort-item'))
                        .map(el => parseInt(el.dataset.id, 10));
                    if (!orden.length) return;
                    this.enviarOrden(orden);
                },
                enviarOrden(orden) {
                    const self = this;
                    self.saving = true;
                    fetch('{{ route("admin.categorias.reordenar") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ orden })
                    }).then(r => r.json()).then(data => {
                        self.finishSave(data && data.success);
                    }).catch(() => self.finishSave(false));
                },
                persistirProductos() {
                    const grupos = [];
                    document.querySelectorAll('.list-items[data-grupo]').forEach(container => {
                        const info = this.parseGrupo(container.dataset.grupo);
                        if (!info) return;
                        const ids = Array.from(container.querySelectorAll(':scope > .list-item[data-producto-id]'))
                            .map(el => parseInt(el.dataset.productoId, 10));
                        if (ids.length) {
                            grupos.push({
                                grupo: container.dataset.grupo,
                                categoria_id: info.categoria_id,
                                productos: ids,
                            });
                        }
                    });
                    const self = this;
                    self.saving = true;
                    fetch('{{ route("admin.productos.reordenar") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ grupos })
                    }).then(r => r.json()).then(data => {
                        self.finishSave(data && data.success);
                    }).catch(() => self.finishSave(false));
                },
                finishSave(ok) {
                    this.saving = false;
                    this.savedMsg = ok;
                    if (!ok) {
                        window.location.reload();
                        return;
                    }
                    const self = this;
                    window.setTimeout(() => { self.savedMsg = false; }, 2500);
                }
            };
        }
    </script>
@endif
@endsection