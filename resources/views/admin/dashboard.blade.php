@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Panel de control</div>
        <div class="page-sub">{{ $totalMesas }} mesas · {{ $totalProductos }} productos · {{ $totalCategorias }} categorías · {{ $totalReservas }} reservas · {{ $totalUsers }} usuarios</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.productos.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo Producto</a>
        <a href="{{ route('reservas.create') }}" class="btn btn-ghost"><i class="fa-regular fa-calendar-plus"></i> Nueva Reserva</a>
    </div>
</div>

<div class="stats-grid">
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Ingresos Hoy</div>
            <div class="scard-icon si-gold"><i class="fa-solid fa-dollar-sign"></i></div>
        </div>
        <div class="scard-val">${{ number_format($ingresosHoy, 0) }}</div>
        <div class="scard-meta {{ $cambioIngresos >= 0 ? 'up' : 'down' }}">
            <i class="fa-solid fa-arrow-{{ $cambioIngresos >= 0 ? 'trend-up' : 'trend-down' }}"></i>
            {{ $cambioIngresos >= 0 ? '+' : '' }}{{ $cambioIngresos }}%<span>vs ayer</span>
        </div>
    </div>
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Cubiertos Hoy</div>
            <div class="scard-icon si-green"><i class="fa-solid fa-user-group"></i></div>
        </div>
        <div class="scard-val">{{ $cubiertosHoy }}</div>
        <div class="scard-meta {{ $cambioCubiertos >= 0 ? 'up' : 'down' }}">
            <i class="fa-solid fa-arrow-{{ $cambioCubiertos >= 0 ? 'trend-up' : 'trend-down' }}"></i>
            {{ $cambioCubiertos >= 0 ? '+' : '' }}{{ $cambioCubiertos }}%<span>vs ayer</span>
        </div>
    </div>
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Ticket Promedio</div>
            <div class="scard-icon si-amber"><i class="fa-solid fa-receipt"></i></div>
        </div>
        <div class="scard-val">${{ number_format($promedioTab) }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-chart-simple"></i> global<span></span></div>
    </div>
    <div class="scard">
        <div class="scard-top">
            <div class="scard-label">Staff en Turno</div>
            <div class="scard-icon si-blue"><i class="fa-solid fa-id-badge"></i></div>
        </div>
        <div class="scard-val">{{ $staffCount }}</div>
        <div class="scard-meta neutral"><i class="fa-solid fa-circle-check" style="color:var(--green-text)"></i> en el sistema<span></span></div>
    </div>
</div>

<div class="mid-grid">
    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Pedidos Activos</div>
                <div class="card-sub">{{ $totalActivos }} abiertos · Últimos 5</div>
            </div>
            <a href="{{ route('pedidos.index') }}" class="card-link">Ver todos <i class="fa-solid fa-arrow-right" style="font-size:9px"></i></a>
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
                        <tr>
                            <td><div class="ucell"><div class="av-sm a1" style="font-size:9px">M{{ $pedido->mesa->numero ?? '?' }}</div><div><strong>Mesa {{ $pedido->mesa->numero ?? '—' }}</strong></div></div></td>
                            <td>{{ $pedido->staff->name ?? '—' }}</td>
                            <td>{{ $pedido->productos->count() }}</td>
                            <td class="amount-cell">${{ number_format($pedido->total, 0) }}</td>
                            <td>
                                @php
                                    $estados = ['pendiente'=>'b-amber','preparando'=>'b-blue','listo'=>'b-green','servido'=>'b-green','pagado'=>'b-gray'];
                                    $badgeClass = $estados[$pedido->estado] ?? 'b-gray';
                                    $label = match($pedido->estado) {
                                        'pendiente' => 'Pendiente', 'preparando' => 'Preparando',
                                        'listo' => 'Listo', 'servido' => 'Servido',
                                        'pagado' => 'Pagado', default => $pedido->estado,
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}"><span class="bdot"></span>{{ $label }}</span>
                            </td>
                            <td style="color:var(--text-3)">{{ $pedido->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-3)">No hay pedidos activos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Plano de Mesas</div>
                <div class="card-sub">{{ count($mesasConPedido) }} ocupadas · {{ count($mesasConReserva) }} reservadas · {{ $mesas->count() - count($mesasConPedido) - count($mesasConReserva) }} libres</div>
            </div>
            <div style="display:flex;gap:10px">
                <a href="{{ route('admin.mesas.plano') }}" class="card-link"><i class="fa-regular fa-pen-to-square"></i> Editar Plano</a>
                <a href="{{ route('admin.mesas.index') }}" class="card-link">Gestionar</a>
            </div>
        </div>
        <div class="table-grid">
            @foreach ($mesas as $mesa)
                @php
                    $estado = 'free';
                    $label = 'Libre';
                    if (in_array($mesa->id, $mesasConPedido)) { $estado = 'occupied'; $label = 'Ocupada'; }
                    elseif (in_array($mesa->id, $mesasConReserva)) { $estado = 'reserved'; $label = 'Reservada'; }
                @endphp
                <div class="table-slot {{ $estado }}">
                    <div class="ts-pax"><i class="fa-solid fa-user"></i> {{ $mesa->capacidad }}</div>
                    <div class="ts-num">{{ $mesa->numero }}</div>
                    <div class="ts-label">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        <div class="map-legend">
            <div class="leg-item"><div class="leg-dot" style="background:var(--gold-glow);border:1px solid var(--gold-dim)"></div>Ocupada</div>
            <div class="leg-item"><div class="leg-dot" style="background:var(--blue-bg);border:1px solid rgba(99,164,216,.25)"></div>Reservada</div>
            <div class="leg-item"><div class="leg-dot" style="background:var(--bg-4);border:1px solid var(--border-dim)"></div>Libre</div>
        </div>
    </div>
</div>

<div class="bottom-grid">
    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Productos Más Vendidos</div>
                <div class="card-sub">Totales globales</div>
            </div>
        </div>
        <div class="drinks-list">
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
                <div class="drink-row"><div class="drink-info"><div class="drink-name" style="color:var(--text-3)">Sin ventas aún</div></div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Productos No Disponibles</div>
                <div class="card-sub">{{ $productosNoDisponibles->count() }} items</div>
            </div>
            <a href="{{ route('admin.productos.index') }}" class="card-link">Gestionar</a>
        </div>
        <div>
            @forelse ($productosNoDisponibles as $producto)
                <div class="inv-row">
                    <div class="inv-icon" style="background:var(--red-bg);color:var(--red-text)"><i class="fa-solid fa-bottle-droplet"></i></div>
                    <div class="inv-info">
                        <div class="inv-name">{{ $producto->nombre }}</div>
                        <div class="inv-stock">Agotado</div>
                        <div class="inv-bar-wrap"><div class="inv-bar" style="width:5%;background:var(--red)"></div></div>
                    </div>
                    <span class="badge b-red">Agotado</span>
                </div>
            @empty
                <div class="inv-row"><div class="inv-info"><div class="inv-name" style="color:var(--text-3)">Todos los productos disponibles</div></div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">Staff</div>
                <div class="card-sub">{{ $staff->count() }} en el sistema</div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="card-link">Ver todos</a>
        </div>
        <div>
            @forelse ($staff as $miembro)
                @php
                    $iniciales = strtoupper(substr($miembro->name, 0, 1) . substr($miembro->name, strpos($miembro->name, ' ') + 1, 1));
                    if (strlen($iniciales) < 2) $iniciales = strtoupper(substr($miembro->name, 0, 2));
                    $rol = match($miembro->role) {
                        'super_admin' => 'Super Admin', 'admin' => 'Admin',
                        'staff' => 'Staff', default => 'Usuario',
                    };
                    $colores = ['#c8922a','#4caf7d','#7c5cc8','#63a4d8','#e05252','#bf8700'];
                    $color = $colores[$loop->index % count($colores)];
                @endphp
                <div class="staff-row">
                    <div class="staff-av" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}dd)">{{ $iniciales }}</div>
                    <div class="staff-info">
                        <div class="staff-name">{{ $miembro->name }}</div>
                        <div class="staff-role">{{ $rol }}</div>
                    </div>
                    <div class="staff-hours">
                        <strong>{{ $miembro->created_at->diffForHumans(now(), true) }}</strong>
                        en el sistema
                    </div>
                </div>
            @empty
                <div class="staff-row"><div class="staff-info"><div class="staff-name" style="color:var(--text-3)">Sin personal registrado</div></div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
