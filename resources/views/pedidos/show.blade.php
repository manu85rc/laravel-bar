@extends('layouts.admin')

@section('content')
    <div class="page-top">
        <div>
            <h1 class="page-title">Pedido #{{ $pedido->id }}</h1>
            <p class="page-sub">Detalle del pedido</p>
        </div>
        <div class="btn-row">
            <a href="{{ route('pedidos.index') }}" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <form action="{{ route('pedidos.printFactura', $pedido) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    <i class="fas fa-print"></i> Factura
                </button>
            </form>
            @if (auth()->user()->isAdmin() || $pedido->estado !== 'pagado')
                <form action="{{ route('pedidos.updateEstado', $pedido) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de cambiar el estado?');">
                    @csrf
                    @method('PUT')
                    <select name="estado" class="form-select" style="width:auto;display:inline-block;padding:6px 28px 6px 10px;font-size:12px;">
                        <option value="pendiente" {{ $pedido->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="preparando" {{ $pedido->estado == 'preparando' ? 'selected' : '' }}>Preparando</option>
                        <option value="listo" {{ $pedido->estado == 'listo' ? 'selected' : '' }}>Listo</option>
                        <option value="servido" {{ $pedido->estado == 'servido' ? 'selected' : '' }}>Servido</option>
                        <option value="pagado" {{ $pedido->estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                    </select>
                    <button type="submit" class="btn btn-gold btn-sm">
                        <i class="fas fa-check"></i> Actualizar
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                @php
                    $estadoBadge = match($pedido->estado) {
                        'pendiente' => 'b-amber',
                        'preparando' => 'b-blue',
                        'listo' => 'b-green',
                        'servido' => 'b-green',
                        'pagado' => 'b-gray',
                        default => 'b-gray'
                    };
                @endphp
                <span class="badge {{ $estadoBadge }}">{{ ucfirst($pedido->estado) }}</span>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <span class="form-label">Mesa</span>
                    <span style="font-size:15px;font-weight:600;color:var(--text-1);">
                        Mesa {{ $pedido->mesa->numero }}
                        @if ($pedido->mesa->ubicacion)
                            <span style="color:var(--text-3);font-weight:400;font-size:13px;">({{ $pedido->mesa->ubicacion }})</span>
                        @endif
                    </span>
                </div>
                <div class="form-group">
                    <span class="form-label">Staff</span>
                    <span style="font-size:15px;font-weight:500;color:var(--text-1);">
                        @if ($pedido->staff)
                            {{ $pedido->staff->name }}
                        @else
                            <span style="color:var(--text-3);">Sin asignar</span>
                        @endif
                    </span>
                </div>
                <div class="form-group">
                    <span class="form-label">Fecha y Hora</span>
                    <span style="font-size:15px;font-weight:500;color:var(--text-1);">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Total</span>
                    <span style="font-size:18px;font-weight:700;color:var(--gold);">${{ number_format($pedido->total, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($pedido->productos->isNotEmpty())
        <div class="card" style="margin-top:16px;">
            <div class="card-head">
                <span class="card-title">Productos</span>
                <span class="card-sub">{{ $pedido->productos->count() }} items</span>
            </div>
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedido->productos as $producto)
                            <tr>
                                <td>{{ $producto->nombre }}</td>
                                <td>{{ $producto->pivot->cantidad }}</td>
                                <td>${{ number_format($producto->pivot->precio_unitario, 0) }}</td>
                                <td style="font-weight:600;color:var(--text-1);">${{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">TOTAL</th>
                            <th style="color:var(--gold);">${{ number_format($pedido->total, 0) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="empty-state" style="margin-top:16px;">
            <i class="fas fa-box-open" style="font-size:24px;margin-bottom:8px;display:block;color:var(--text-4);"></i>
            No hay productos en este pedido.
        </div>
    @endif
@endsection