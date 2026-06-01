@extends('layouts.admin')

@section('content')
    <div class="page-top">
        <div>
            <h1 class="page-title">Pedidos</h1>
            <p class="page-sub">Gestiona los pedidos de las mesas</p>
        </div>
        <div class="btn-row">
            <a href="{{ route('pedidos.create') }}" class="btn btn-gold">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($pedidos->isEmpty())
        <div class="empty-state">
            <i class="fas fa-receipt" style="font-size:32px;margin-bottom:12px;display:block;color:var(--text-4);"></i>
            No hay pedidos registrados.
        </div>
    @else
        <div class="card">
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mesa</th>
                            <th>Staff</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td style="font-weight:500;color:var(--text-1);">{{ $pedido->id }}</td>
                                <td>Mesa {{ $pedido->mesa->numero }}</td>
                                <td>
                                    @if ($pedido->staff)
                                        <span class="ucell">
                                            <i class="fas fa-user" style="color:var(--text-3);width:16px;"></i>
                                            {{ $pedido->staff->name }}
                                        </span>
                                    @else
                                        <span style="color:var(--text-4);">Sin asignar</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($pedido->estado) {
                                            'pendiente' => 'b-amber',
                                            'preparando' => 'b-blue',
                                            'listo' => 'b-green',
                                            'servido' => 'b-green',
                                            'pagado' => 'b-gray',
                                            default => 'b-gray'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        <span class="bdot"></span>
                                        {{ ucfirst($pedido->estado) }}
                                    </span>
                                </td>
                                <td style="font-weight:600;color:var(--text-1);">${{ number_format($pedido->total, 0) }}</td>
                                <td style="text-align:right;">
                                    <div class="list-item-actions" style="justify-content:flex-end;">
                                        <a href="{{ route('pedidos.show', $pedido) }}">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        @if (auth()->user()->isAdmin() || $pedido->estado === 'pendiente')
                                            <form action="{{ route('pedidos.updateEstado', $pedido) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de cambiar el estado?');">
                                                @csrf
                                                @method('PUT')
                                                <select name="estado" class="form-select" style="width:auto;display:inline-block;padding:4px 24px 4px 8px;font-size:11px;">
                                                    <option value="pendiente" {{ $pedido->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="preparando" {{ $pedido->estado == 'preparando' ? 'selected' : '' }}>Preparando</option>
                                                    <option value="listo" {{ $pedido->estado == 'listo' ? 'selected' : '' }}>Listo</option>
                                                    <option value="servido" {{ $pedido->estado == 'servido' ? 'selected' : '' }}>Servido</option>
                                                    <option value="pagado" {{ $pedido->estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                                </select>
                                                <button type="submit" class="btn-link" style="font-size:11px;">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection