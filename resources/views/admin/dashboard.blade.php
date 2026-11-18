@extends('layouts.admin')

@section('content')
@php
    $estados = [
        'pendiente' => ['label' => 'Pendiente', 'badge' => 'b-amber'],
        'preparando' => ['label' => 'Preparando', 'badge' => 'b-blue'],
        'listo' => ['label' => 'Listo', 'badge' => 'b-green'],
        'servido' => ['label' => 'Servido', 'badge' => 'b-green'],
        'pagado' => ['label' => 'Pagado', 'badge' => 'b-gray'],
    ];

    $totalMesasCount = max(1, $mesas->count());
    $ocupadas = count($mesasConPedido);
    $reservadas = count($mesasConReserva);
    $libres = max(0, $mesas->count() - $ocupadas - $reservadas);
    $pctOcupadas = round($ocupadas / $totalMesasCount * 100);
    $pctReservadas = round($reservadas / $totalMesasCount * 100);
    $pctLibres = round($libres / $totalMesasCount * 100);
@endphp

<div class="page-top">
    <div>
        <h1 class="page-title">Panel de control</h1>
        <p class="page-sub">
            {{ $totalMesas }} mesas · {{ $totalProductos }} productos · {{ $totalCategorias }} categorías · {{ $totalReservas }} reservas · {{ $totalUsers }} usuarios
        </p>
    </div>
    <div class="btn-row">
        <div class="mesa-view-tabs" style="margin:0;">
            @foreach ([7 => '7 días', 30 => '30 días'] as $value => $lbl)
                <a href="{{ route('dashboard', ['range' => $value]) }}" class="mesa-view-tab{{ $range === $value ? ' active' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.productos.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo producto</a>
    </div>
</div>

{{-- KPIs --}}
<div class="stats-grid">
    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Ingresos hoy</span>
            <span class="scard-icon si-gold"><i class="fa-solid fa-dollar-sign"></i></span>
        </div>
        <div class="scard-val">${{ number_format($ingresosHoy, 0) }}</div>
        <div class="scard-meta {{ $cambioIngresos >= 0 ? 'up' : 'down' }}">
            <i class="fa-solid fa-arrow-trend-{{ $cambioIngresos >= 0 ? 'up' : 'down' }}"></i>
            {{ $cambioIngresos >= 0 ? '+' : '' }}{{ $cambioIngresos }}%<span>vs ayer</span>
        </div>
    </div>

    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Cubiertos hoy</span>
            <span class="scard-icon si-green"><i class="fa-solid fa-user-group"></i></span>
        </div>
        <div class="scard-val">{{ $cubiertosHoy }}</div>
        <div class="scard-meta {{ $cambioCubiertos >= 0 ? 'up' : 'down' }}">
            <i class="fa-solid fa-arrow-trend-{{ $cambioCubiertos >= 0 ? 'up' : 'down' }}"></i>
            {{ $cambioCubiertos >= 0 ? '+' : '' }}{{ $cambioCubiertos }}%<span>vs ayer</span>
        </div>
    </div>

    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Ticket promedio</span>
            <span class="scard-icon si-amber"><i class="fa-solid fa-receipt"></i></span>
        </div>
        <div class="scard-val">${{ number_format($promedioTab, 0) }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-minus"></i> últimos {{ $range }} días</div>
    </div>

    <div class="scard">
        <div class="scard-top">
            <span class="scard-label">Staff en turno</span>
            <span class="scard-icon si-blue"><i class="fa-solid fa-id-badge"></i></span>
        </div>
        <div class="scard-val">{{ $staffCount }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-minus"></i> en el sistema</div>
    </div>
</div>

{{-- Tendencia + ocupación --}}
<div class="mid-grid">
    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Tendencia de ingresos</div>
                <div class="card-sub">Últimos {{ $range }} días</div>
            </div>
            <div class="amount-cell">${{ number_format(array_sum($ingresosSerie), 0) }}</div>
        </div>
        <div class="card-body">
            <x-trend-chart :data="$ingresosSerie" :labels="$chartLabels" :height="180" />
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Ocupación de salón</div>
                <div class="card-sub">{{ $mesas->count() }} mesas</div>
            </div>
            <a href="{{ route('admin.mesas.plano') }}" class="card-link">Ver plano</a>
        </div>
        <div class="card-body">
            <div class="occ-bar">
                <div class="occ-seg" style="width:{{ $pctOcupadas }}%;background:var(--gold);"></div>
                <div class="occ-seg" style="width:{{ $pctReservadas }}%;background:var(--blue);"></div>
                <div class="occ-seg" style="width:{{ $pctLibres }}%;background:var(--green);"></div>
            </div>
            <div class="occ-legend">
                <div class="leg-item"><span class="leg-dot" style="background:var(--gold);"></span> Ocupadas <strong>{{ $ocupadas }}</strong></div>
                <div class="leg-item"><span class="leg-dot" style="background:var(--blue);"></span> Reservadas <strong>{{ $reservadas }}</strong></div>
                <div class="leg-item"><span class="leg-dot" style="background:var(--green);"></span> Libres <strong>{{ $libres }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- Pedidos activos --}}
<div class="card" style="margin-bottom:22px;">
    <div class="card-head">
        <div>
            <div class="card-title">Pedidos activos</div>
            <div class="card-sub">{{ $totalActivos }} abiertos</div>
        </div>
        <a href="{{ route('pedidos.index') }}" class="card-link">Ver todos →</a>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mesa</th>
                    <th>Mesero</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Desde</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidosActivos as $pedido)
                    @php $estado = $estados[$pedido->estado] ?? ['label' => $pedido->estado, 'badge' => 'b-gray']; @endphp
                    <tr>
                        <td><strong>Mesa {{ $pedido->mesa->numero ?? '—' }}</strong></td>
                        <td>{{ $pedido->staff->name ?? '—' }}</td>
                        <td>{{ $pedido->productos->count() }}</td>
                        <td class="amount-cell">${{ number_format($pedido->total, 0) }}</td>
                        <td><span class="badge {{ $estado['badge'] }}"><span class="bdot"></span>{{ $estado['label'] }}</span></td>
                        <td>{{ $pedido->created_at->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><div class="empty-state">Sin pedidos activos. Todo al día.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Secundario --}}
<div class="bottom-grid">
    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Más vendidos</div>
                <div class="card-sub">Totales globales</div>
            </div>
        </div>
        @forelse ($topProductos as $i => $item)
            <div class="drink-row">
                <div class="drink-rank {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</div>
                <div class="drink-info">
                    <div class="drink-name">{{ $item['nombre'] }}</div>
                    <div class="drink-cat">{{ $item['categoria'] }}</div>
                </div>
                <div class="drink-right">
                    <div class="drink-count">{{ $item['cantidad'] }} u.</div>
                    <div class="drink-rev">${{ number_format($item['total']) }}</div>
                </div>
            </div>
        @empty
            <div class="empty-state">Sin ventas aún.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">No disponibles</div>
                <div class="card-sub">{{ $productosNoDisponibles->count() }} items</div>
            </div>
            <a href="{{ route('admin.menu.index') }}" class="card-link">Gestionar</a>
        </div>
        @forelse ($productosNoDisponibles as $producto)
            <div class="inv-row">
                <span class="inv-icon" style="background:var(--red-bg);color:var(--red-text);"><i class="fa-solid fa-bottle-droplet"></i></span>
                <div class="inv-info"><div class="inv-name">{{ $producto->nombre }}</div></div>
                <span class="badge b-red"><span class="bdot"></span>Agotado</span>
            </div>
        @empty
            <div class="empty-state">Todo disponible.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Staff</div>
                <div class="card-sub">{{ $staff->count() }} en el sistema</div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="card-link">Ver todos</a>
        </div>
        @forelse ($staff as $miembro)
            @php
                $iniciales = strtoupper(substr($miembro->name, 0, 1).substr($miembro->name, strpos($miembro->name, ' ') + 1, 1));
                if (strlen($iniciales) < 2) { $iniciales = strtoupper(substr($miembro->name, 0, 2)); }
                $rol = $miembro->role?->name ?? 'Usuario';
            @endphp
            <div class="staff-row">
                <span class="staff-av a1">{{ $iniciales }}</span>
                <div class="staff-info">
                    <div class="staff-name">{{ $miembro->name }}</div>
                    <div class="staff-role">{{ $rol }}</div>
                </div>
                <div class="staff-hours"><strong>{{ $miembro->created_at->diffForHumans(now(), true) }}</strong>en el sistema</div>
            </div>
        @empty
            <div class="empty-state">Sin personal registrado.</div>
        @endforelse
    </div>
</div>
@endsection
