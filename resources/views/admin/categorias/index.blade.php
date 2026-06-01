@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Gestión de Categorías</div>
        <div class="page-sub">{{ $categorias->count() }} categorías</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.categorias.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nueva Categoría</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if ($categorias->isEmpty())
    <div class="empty-state">No hay categorías principales. ¡Crea una nueva!</div>
@else
<div class="card" x-data="categoriasReordenar()">
    <div class="card-body compact">
        <template x-if="dirty">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:var(--gold-glow);border-radius:var(--radius);border:1px solid var(--border)">
                <span style="font-size:12px;color:var(--gold-light)">Orden modificado</span>
                <button @click="guardarOrden()" class="btn btn-gold btn-sm">Guardar orden</button>
            </div>
        </template>

        <div id="categorias-sortable" class="list-items">
            @foreach ($categorias as $categoria)
                <div class="list-item sort-item"
                     data-id="{{ $categoria->id }}"
                     @dragstart="onDragStart($event, {{ $categoria->id }})"
                     @dragover.prevent="onDragOver($event, {{ $categoria->id }})"
                     @dragend="onDragEnd()"
                     draggable="true"
                     :class="{ dragging: draggingId === {{ $categoria->id }} }">
                    <div class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></div>
                    <div class="av-sm a1" style="font-size:10px;width:32px;height:32px;border-radius:var(--radius)">{{ strtoupper(substr($categoria->name,0,1)) }}</div>
                    <div class="list-item-info">
                        <div class="list-item-title">{{ $categoria->name }}</div>
                        <div class="list-item-sub">
                            @if ($categoria->description) {{ $categoria->description }} · @endif
                            orden: {{ $categoria->orden }}
                            @if ($categoria->children->count() > 0)
                                · Hijas: {{ $categoria->children->pluck('name')->implode(', ') }}
                            @endif
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <a href="{{ route('admin.categorias.edit', $categoria->id) }}">Editar</a>
                        <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría y todas sus subcategorías?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-link danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<script>
    function categoriasReordenar() {
        return {
            draggingId: null,
            dragOverId: null,
            dirty: false,
            ordenOriginal: [],
            init() {
                const items = document.querySelectorAll('#categorias-sortable > div');
                this.ordenOriginal = Array.from(items).map(el => parseInt(el.dataset.id));
            },
            onDragStart(event, id) {
                this.draggingId = id;
                event.dataTransfer.effectAllowed = 'move';
            },
            onDragOver(event, id) {
                if (this.draggingId === id) return;
                this.dragOverId = id;
                const container = document.getElementById('categorias-sortable');
                const draggingEl = container.querySelector(`[data-id="${this.draggingId}"]`);
                const targetEl = container.querySelector(`[data-id="${id}"]`);
                if (draggingEl && targetEl) {
                    const rect = targetEl.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;
                    if (event.clientY < midY) {
                        container.insertBefore(draggingEl, targetEl);
                    } else {
                        container.insertBefore(draggingEl, targetEl.nextSibling);
                    }
                }
                this.dirty = true;
            },
            onDragEnd() {
                this.draggingId = null;
                this.dragOverId = null;
            },
            guardarOrden() {
                const items = document.querySelectorAll('#categorias-sortable > div');
                const orden = Array.from(items).map(el => parseInt(el.dataset.id));
                fetch('{{ route("admin.categorias.reordenar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ orden })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        this.dirty = false;
                        this.ordenOriginal = orden;
                    }
                });
            }
        };
    }
</script>
@endsection
