    <div class="mesa-view-tabs">
        <a href="{{ route('pedidos.index', array_merge(request()->query(), ['tab' => 'activos'])) }}" class="mesa-view-tab {{ $tab === 'activos' ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i> Activos @if ($activosCount > 0)<span class="nav-chip">{{ $activosCount }}</span>@endif</a>
        <a href="{{ route('pedidos.index', array_merge(request()->query(), ['tab' => 'historial'])) }}" class="mesa-view-tab {{ $tab === 'historial' ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Historial @if ($historialCount > 0)<span class="nav-chip">{{ $historialCount }}</span>@endif</a>
        @if ($puedeVerMp)
        <a href="{{ route('pedidos.index', array_merge(request()->query(), ['tab' => 'mercadopago'])) }}" class="mesa-view-tab {{ $tab === 'mercadopago' ? 'active' : '' }}"><x-mp-icon /> Mercado Pago</a>
        @endif
    </div>

    @if ($tab === 'activos')
        @if ($grupos->isEmpty())
            <div class="empty-state">
                <i class="fas fa-receipt" style="font-size:32px;margin-bottom:12px;display:block;color:var(--text-4);"></i>
                No hay pedidos activos.
            </div>
        @else
            <div class="card">
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Mesa</th>
                                <th>Pedidos</th>
                                <th>Staff</th>
                                <th>Estados</th>
                                <th>Total</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grupos as $indice => $grupo)
                                @php
                                    $staffNombres = $grupo['pedidos']->pluck('staff.name')->filter()->unique();
                                    $estados = $grupo['pedidos']->pluck('estado')->unique();
                                @endphp
                                <tr class="pedido-grupo" data-indice="{{ $indice }}" onclick="togglePedidoGrupo({{ $indice }})">
                                    <td>
                                        <i class="fas fa-chevron-right pedido-grupo-icon" style="font-size:11px;color:var(--text-4);margin-right:8px;transition:transform .15s;"></i>
                                        <strong>{{ $grupo['mesa'] ? 'Mesa '.$grupo['mesa']->numero : 'Sin mesa' }}</strong>
                                        @if ($grupo['abiertaDesde'])
                                            <div style="font-size:11px;color:var(--text-4);margin-top:3px;">
                                                <i class="fa-regular fa-clock" style="width:12px;display:inline-block;text-align:center;"></i>
                                                Abierta desde {{ $grupo['abiertaDesde']->format('H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $grupo['count'] > 1 ? 'b-blue' : 'b-gray' }}">
                                            {{ $grupo['count'] }} {{ $grupo['count'] === 1 ? 'pedido' : 'pedidos' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($staffNombres->isNotEmpty())
                                            <span style="color:var(--text-2);">{{ $staffNombres->implode(', ') }}</span>
                                        @else
                                            <span style="color:var(--text-4);">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($estados as $estado)
                                            @php
                                                $badgeClass = match($estado) {
                                                    'pendiente' => 'b-amber',
                                                    'preparando' => 'b-blue',
                                                    'listo' => 'b-green',
                                                    'servido' => 'b-green',
                                                    'pagado' => 'b-gray',
                                                    'cancelado' => 'b-red',
                                                    default => 'b-gray'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" style="margin-right:4px;">
                                                <span class="bdot"></span>{{ ucfirst($estado) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td style="font-weight:700;color:var(--text-1);">
                                        ${{ number_format($grupo['totalSum'], 0) }}
                                        @if ($grupo['completa'])
                                            <div class="pago-estado ok"><i class="fas fa-check"></i> Pagado</div>
                                        @else
                                            <div class="pago-estado pend">Pendiente ${{ number_format($grupo['pendienteSum'], 2) }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="list-item-actions" style="justify-content:flex-end;">
                                            <a href="{{ route('pedidos.create') }}">
                                                <i class="fas fa-plus"></i> Nuevo Pedido
                                            </a>
                                            @if ($grupo['mesa'])
                                                @if ($grupo['completa'])
                                                    <form action="{{ route('pedidos.cerrarMesa', $grupo['mesa']) }}" method="POST" class="inline" onclick="event.stopPropagation();" onsubmit="return confirm('¿Cerrar la mesa? Los pedidos pasarán a pagados.');">
                                                        @csrf
                                                        <button type="submit" class="btn-link" style="color:var(--green-text);">
                                                            <i class="fas fa-lock"></i> Cerrar mesa
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn-link" onclick="event.stopPropagation();abrirPagar({{ $indice }});">
                                                        <i class="fas fa-cash-register"></i> Pagar
                                                    </button>
                                                @endif
                                                <form action="{{ route('pedidos.printFacturaMesa', $grupo['mesa']) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn-link" onclick="event.stopPropagation();">
                                                        <i class="fas fa-print"></i> Factura
                                                    </button>
                                                </form>
                                            @endif
                                            <span class="btn-link" style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;" onclick="event.stopPropagation();togglePedidoGrupo({{ $indice }})">
                                                <i class="fas fa-eye"></i> Ver
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="pedido-grupo-detalle" data-detalle="{{ $indice }}" style="display:none;">
                                    <td colspan="6" style="padding:0;">
                                        <table class="pedido-grupo-tabla">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Hora</th>
                                                    <th>Staff</th>
                                                    <th>Estado</th>
                                                    <th>Total</th>
                                                    <th style="text-align:right;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($grupo['pedidos'] as $pedido)
                                                    <tr>
                                                        <td style="font-weight:500;color:var(--text-1);">{{ $pedido->id }}</td>
                                                        <td style="color:var(--text-2);white-space:nowrap;">{{ $pedido->created_at->format('H:i') }}</td>
                                                        <td>
                                                            @if ($pedido->staff)
                                                                <span class="ucell">
                                                                    @if ($pedido->staff->profilePhotoUrl())
                                                                        <img src="{{ $pedido->staff->profilePhotoUrl() }}" alt="{{ $pedido->staff->name }}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">
                                                                    @else
                                                                        <i class="fas fa-user" style="color:var(--text-3);width:16px;"></i>
                                                                    @endif
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
                                                                    'cancelado' => 'b-red',
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
                                                                @if (! in_array($pedido->estado, ['servido', 'pagado', 'cancelado']))
                                                                    <form action="{{ route('pedidos.updateEstado', $pedido) }}" method="POST" class="inline">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="estado" value="servido">
                                                                        <button type="submit" class="btn-link" style="font-size:11px;">
                                                                            <i class="fa-solid fa-utensils"></i> Servido
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" style="padding:0;">
                                                            <div class="linea-chips">
                                                                @foreach ($grupo['lineas'] as $linea)
                                                                    @if ($linea['pedido_id'] === $pedido->id)
                                                                        <span class="linea-chip {{ $linea['pagado'] ? 'pagado' : '' }}">
                                                                            @if ($linea['pagado'])<i class="fas fa-check"></i>@endif
                                                                            {{ $linea['cantidad'] }}&times; {{ $linea['producto'] }}
                                                                            <span class="lc-sub">${{ number_format($linea['subtotal'], 0) }}</span>
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @elseif ($tab === 'historial')
        @if ($pedidos->isEmpty())
            <div class="empty-state">
                <i class="fas fa-receipt" style="font-size:32px;margin-bottom:12px;display:block;color:var(--text-4);"></i>
                No hay pedidos en el historial.
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
                                <th>Fecha</th>
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
                                                @if ($pedido->staff->profilePhotoUrl())
                                                    <img src="{{ $pedido->staff->profilePhotoUrl() }}" alt="{{ $pedido->staff->name }}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">
                                                @else
                                                    <i class="fas fa-user" style="color:var(--text-3);width:16px;"></i>
                                                @endif
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
                                                'cancelado' => 'b-red',
                                                default => 'b-gray'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            <span class="bdot"></span>
                                            {{ ucfirst($pedido->estado) }}
                                        </span>
                                    </td>
                                    <td style="color:var(--text-2);">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                    <td style="font-weight:600;color:var(--text-1);">${{ number_format($pedido->total, 0) }}</td>
                                    <td style="text-align:right;">
                                        <div class="list-item-actions" style="justify-content:flex-end;">
                                            <a href="{{ route('pedidos.show', $pedido) }}">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @elseif ($tab === 'mercadopago')
        @php
            $cfg = $mpSettings;
        @endphp
        @if (! $cfg || ! $cfg->access_token)
            <div class="empty-state">
                <x-mp-icon :size="32" style="margin:0 auto 12px;display:block;color:var(--text-4);" />
                Mercado Pago aún no está configurado.
                <div style="font-size:12px;margin-top:6px;">Cargá el access token en <strong>Config → Mercado Pago</strong> para ver los movimientos.</div>
            </div>
        @elseif ($mpMovimientos->isEmpty())
            <div class="empty-state">
                <i class="fas fa-infinity" style="font-size:32px;margin-bottom:12px;display:block;color:var(--text-4);"></i>
                No hay movimientos recientes en Mercado Pago.
            </div>
        @else
            <div class="card">
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                @if ($cfg->mostrar_fecha)<th>Fecha</th>@endif
                                @if ($cfg->mostrar_id)<th>ID</th>@endif
                                @if ($cfg->mostrar_pagador)<th>Pagador</th>@endif
                                @if ($cfg->mostrar_estado)<th>Estado</th>@endif
                                @if ($cfg->mostrar_metodo)<th>Método</th>@endif
                                @if ($cfg->mostrar_tipo)<th>Tipo</th>@endif
                                @if ($cfg->mostrar_descripcion)<th>Descripción</th>@endif
                                @if ($cfg->mostrar_monto)<th style="text-align:right;">Monto</th>@endif
                                @if ($cfg->mostrar_detalle)<th style="text-align:right;">Detalle</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mpMovimientos as $mov)
                                @php
                                    $badgeEstado = match ($mov['estado']) {
                                        'approved' => 'b-green',
                                        'pending', 'in_process', 'in_mediation' => 'b-amber',
                                        'authorized' => 'b-blue',
                                        'rejected', 'cancelled' => 'b-red',
                                        'refunded', 'charged_back' => 'b-blue',
                                        default => 'b-gray',
                                    };
                                @endphp
                                <tr>
                                    @if ($cfg->mostrar_fecha)<td style="color:var(--text-2);white-space:nowrap;">{{ $mov['fecha']->format('d/m/Y H:i') }}</td>@endif
                                    @if ($cfg->mostrar_id)<td style="font-family:monospace;font-size:12px;color:var(--text-3);">{{ $mov['id'] }}</td>@endif
                                    @if ($cfg->mostrar_pagador)
                                        @php
                                            $pagadorDetalle = collect([$mov['pagador_email'], $mov['pagador_documento']])
                                                ->filter()
                                                ->reject(fn ($valor) => $valor === $mov['pagador'])
                                                ->unique()
                                                ->implode(' · ');
                                        @endphp
                                        <td style="max-width:220px;">
                                            <div style="color:var(--text-1);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mov['pagador'] }}">{{ $mov['pagador'] }}</div>
                                            @if ($pagadorDetalle !== '')
                                                <div style="font-size:11px;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $pagadorDetalle }}">{{ $pagadorDetalle }}</div>
                                            @endif
                                        </td>
                                    @endif
                                    @if ($cfg->mostrar_estado)
                                        <td>
                                            <span class="badge {{ $badgeEstado }}">
                                                <span class="bdot"></span>{{ $mov['estado_label'] }}
                                            </span>
                                        </td>
                                    @endif
                                    @if ($cfg->mostrar_metodo)<td style="color:var(--text-2);" title="{{ $mov['metodo'] }}">{{ $mov['metodo_label'] }}</td>@endif
                                    @if ($cfg->mostrar_tipo)
                                        <td>
                                            <span class="badge {{ $mov['tipo'] === 'egreso' ? 'b-red' : 'b-green' }}">
                                                {{ $mov['tipo'] === 'egreso' ? 'Egreso' : 'Ingreso' }}
                                            </span>
                                        </td>
                                    @endif
                                    @if ($cfg->mostrar_descripcion)<td style="color:var(--text-2);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mov['descripcion'] }}">{{ $mov['descripcion'] }}</td>@endif
                                    @if ($cfg->mostrar_monto)
                                        <td style="font-weight:700;color:var(--text-1);text-align:right;white-space:nowrap;">
                                            @if ($mov['tipo'] === 'egreso')-@endif${{ number_format(abs($mov['monto']), 2) }}
                                        </td>
                                    @endif
                                    @if ($cfg->mostrar_detalle)
                                        <td style="text-align:right;">
                                            @if ($mov['detalle_url'])
                                                <a href="{{ $mov['detalle_url'] }}" target="_blank" rel="noopener" class="btn-link">
                                                    <i class="fas fa-arrow-up-right-from-square"></i> Ver
                                                </a>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
