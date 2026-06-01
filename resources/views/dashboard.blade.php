@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Dashboard</div>
        <div class="page-sub">Bienvenido, {{ auth()->user()->name }}</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('reservas.create') }}" class="btn btn-gold"><i class="fa-regular fa-calendar-plus"></i> Nueva Reserva</a>
    </div>
</div>

<div class="stats-grid">
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Reservas Activas</div>
            <div class="scard-icon si-amber"><i class="fa-regular fa-calendar"></i></div>
        </div>
        <div class="scard-val">{{ $reservasActivas }}</div>
        <div class="scard-meta neutral"><i class="fa-regular fa-clock"></i><span>pendientes o confirmadas</span></div>
    </div>
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Total Reservas</div>
            <div class="scard-icon si-blue"><i class="fa-regular fa-calendar-check"></i></div>
        </div>
        <div class="scard-val">{{ $totalReservas }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-arrow-trend-up"></i><span>histórico completo</span></div>
    </div>
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Pedidos Activos</div>
            <div class="scard-icon si-green"><i class="fa-solid fa-mug-saucer"></i></div>
        </div>
        <div class="scard-val">{{ $pedidosActivos }}</div>
        <div class="scard-meta neutral"><i class="fa-regular fa-bell"></i><span>a tu cargo</span></div>
    </div>
</div>

@if ($misReservas->isNotEmpty())
<div class="card" style="margin-bottom:18px">
    <div class="card-head">
        <div>
            <div class="card-title">Tus próximas reservas</div>
            <div class="card-sub">Últimas 5 reservas</div>
        </div>
        <a href="{{ route('reservas.index') }}" class="card-link">Ver todas <i class="fa-solid fa-arrow-right" style="font-size:9px"></i></a>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mesa</th>
                    <th>Fecha y Hora</th>
                    <th>Personas</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($misReservas as $reserva)
                    @php
                        $estadoClasses = match($reserva->estado) {
                            'pendiente' => 'b-amber',
                            'confirmada' => 'b-blue',
                            'completada' => 'b-green',
                            default => 'b-red',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $reserva->mesa ? 'Mesa ' . $reserva->mesa->numero : '—' }}</strong></td>
                        <td>{{ $reserva->fecha_hora }}</td>
                        <td>{{ $reserva->numero_personas }}</td>
                        <td><span class="badge {{ $estadoClasses }}"><span class="bdot"></span>{{ ucfirst($reserva->estado) }}</span></td>
                        <td>{{ $reserva->observaciones ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card" style="margin-bottom:18px">
    <div class="card-head">
        <div>
            <div class="card-title">Tus próximas reservas</div>
            <div class="card-sub">No tienes reservas aún</div>
        </div>
        <a href="{{ route('reservas.create') }}" class="card-link">Crear reserva <i class="fa-solid fa-arrow-right" style="font-size:9px"></i></a>
    </div>
    <div class="empty-state">
        <i class="fa-regular fa-calendar" style="font-size:24px;margin-bottom:8px;display:block;color:var(--text-4)"></i>
        Aún no has realizado ninguna reserva.
    </div>
</div>
@endif

@if ($misPedidos->isNotEmpty())
<div class="card">
    <div class="card-head">
        <div>
            <div class="card-title">Tus pedidos activos</div>
            <div class="card-sub">{{ $pedidosActivos }} pedidos a tu cargo</div>
        </div>
        <a href="{{ route('pedidos.index') }}" class="card-link">Ver todos <i class="fa-solid fa-arrow-right" style="font-size:9px"></i></a>
    </div>
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
                    @php
                        $estados = ['pendiente'=>'b-amber','preparando'=>'b-blue','listo'=>'b-green','servido'=>'b-green','pagado'=>'b-gray'];
                        $badgeClass = $estados[$pedido->estado] ?? 'b-gray';
                        $label = match($pedido->estado) {
                            'pendiente' => 'Pendiente', 'preparando' => 'Preparando',
                            'listo' => 'Listo', 'servido' => 'Servido',
                            'pagado' => 'Pagado', default => $pedido->estado,
                        };
                    @endphp
                    <tr>
                        <td><strong>Mesa {{ $pedido->mesa->numero ?? '—' }}</strong></td>
                        <td>{{ $pedido->productos->count() }}</td>
                        <td class="amount-cell">${{ number_format($pedido->total, 0) }}</td>
                        <td><span class="badge {{ $badgeClass }}"><span class="bdot"></span>{{ $label }}</span></td>
                        <td style="color:var(--text-3)">{{ $pedido->created_at->format('H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
