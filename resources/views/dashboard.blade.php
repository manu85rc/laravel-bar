@extends('layouts.admin')

@section('content')
@php
    $estadosReserva = [
        'pendiente' => ['label' => 'Pendiente', 'badge' => 'b-amber'],
        'confirmada' => ['label' => 'Confirmada', 'badge' => 'b-blue'],
        'completada' => ['label' => 'Completada', 'badge' => 'b-green'],
        'cancelada' => ['label' => 'Cancelada', 'badge' => 'b-red'],
    ];
    $estadosPedido = [
        'pendiente' => ['label' => 'Pendiente', 'badge' => 'b-amber'],
        'preparando' => ['label' => 'Preparando', 'badge' => 'b-blue'],
        'listo' => ['label' => 'Listo', 'badge' => 'b-green'],
        'servido' => ['label' => 'Servido', 'badge' => 'b-green'],
        'pagado' => ['label' => 'Pagado', 'badge' => 'b-gray'],
    ];
@endphp

<div class="page-top">
    <div>
        <h1 class="page-title">Hola, {{ auth()->user()->name }}</h1>
        <p class="page-sub">Tu resumen de actividad</p>
    </div>
    <div class="btn-row">
        <div class="mesa-view-tabs" style="margin:0;">
            @foreach ([7 => '7 días', 30 => '30 días'] as $value => $lbl)
                <a href="{{ route('dashboard', ['range' => $value]) }}" class="mesa-view-tab{{ $range === $value ? ' active' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>
        <a href="{{ route('reservas.create') }}" class="btn btn-gold"><i class="fa-regular fa-calendar-plus"></i> Nueva reserva</a>
    </div>
</div>

{{-- KPIs --}}
<div class="stats-grid cols-3">
    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Reservas activas</span>
            <span class="scard-icon si-gold"><i class="fa-regular fa-calendar"></i></span>
        </div>
        <div class="scard-val">{{ $reservasActivas }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-minus"></i> pendientes o confirmadas</div>
    </div>

    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Total reservas</span>
            <span class="scard-icon si-blue"><i class="fa-regular fa-calendar-check"></i></span>
        </div>
        <div class="scard-val">{{ $totalReservas }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-minus"></i> histórico completo</div>
    </div>

    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Pedidos activos</span>
            <span class="scard-icon si-green"><i class="fa-solid fa-mug-saucer"></i></span>
        </div>
        <div class="scard-val">{{ $pedidosActivos }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-minus"></i> a tu cargo</div>
    </div>
</div>

{{-- Actividad --}}
<div class="card" style="margin-bottom:22px;">
    <div class="card-head">
        <div>
            <div class="card-title">Tu actividad</div>
            <div class="card-sub">Reservas de los últimos {{ $range }} días</div>
        </div>
        <div class="amount-cell">{{ array_sum($reservasSerie) }}</div>
    </div>
    <div class="card-body">
        <x-trend-chart :data="$reservasSerie" :labels="$chartLabels" :height="160" />
    </div>
</div>

{{-- Próximas reservas --}}
<div class="card" style="margin-bottom:22px;">
    <div class="card-head">
        <div>
            <div class="card-title">Tus próximas reservas</div>
            <div class="card-sub">Últimas 5</div>
        </div>
        <a href="{{ route('reservas.index') }}" class="card-link">Ver todas →</a>
    </div>
    @if ($misReservas->isNotEmpty())
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mesa</th>
                        <th>Fecha y hora</th>
                        <th>Personas</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($misReservas as $reserva)
                        @php $estado = $estadosReserva[$reserva->estado] ?? ['label' => $reserva->estado, 'badge' => 'b-gray']; @endphp
                        <tr>
                            <td><strong>{{ $reserva->mesa ? 'Mesa '.$reserva->mesa->numero : '—' }}</strong></td>
                            <td>{{ $reserva->fecha_hora }}</td>
                            <td>{{ $reserva->numero_personas }}</td>
                            <td><span class="badge {{ $estado['badge'] }}"><span class="bdot"></span>{{ $estado['label'] }}</span></td>
                            <td>{{ $reserva->observaciones ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">Aún no tienes reservas. Crea tu primera reserva para verla aquí.</div>
    @endif
</div>

{{-- Pedidos activos --}}
<div class="card">
    <div class="card-head">
        <div>
            <div class="card-title">Tus pedidos activos</div>
            <div class="card-sub">{{ $pedidosActivos }} a tu cargo</div>
        </div>
        <a href="{{ route('pedidos.index') }}" class="card-link">Ver todos →</a>
    </div>
    @if ($misPedidos->isNotEmpty())
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mesa</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Desde</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($misPedidos as $pedido)
                        @php $estado = $estadosPedido[$pedido->estado] ?? ['label' => $pedido->estado, 'badge' => 'b-gray']; @endphp
                        <tr>
                            <td><strong>Mesa {{ $pedido->mesa->numero ?? '—' }}</strong></td>
                            <td>{{ $pedido->productos->count() }}</td>
                            <td class="amount-cell">${{ number_format($pedido->total, 0) }}</td>
                            <td><span class="badge {{ $estado['badge'] }}"><span class="bdot"></span>{{ $estado['label'] }}</span></td>
                            <td>{{ $pedido->created_at->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">No tienes pedidos a tu cargo.</div>
    @endif
</div>
@endsection
