@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Mis Reservas</div>
        <div class="page-sub">@if ($tab === 'historial') Reservas de los últimos 30 días @else {{ $proximasCount }} reserva(s) próximas @endif</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('reservas.create') }}" class="btn btn-gold"><i class="fa-regular fa-calendar-plus"></i> Nueva Reserva</a>
    </div>
</div>

<div class="mesa-view-tabs">
    <a href="{{ route('reservas.index', ['tab' => 'proximas']) }}" class="mesa-view-tab {{ $tab === 'proximas' ? 'active' : '' }}"><i class="fa-regular fa-calendar"></i> Próximas @if ($proximasCount > 0)<span class="nav-chip">{{ $proximasCount }}</span>@endif</a>
    <a href="{{ route('reservas.index', ['tab' => 'historial']) }}" class="mesa-view-tab {{ $tab === 'historial' ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Historial @if ($historialCount > 0)<span class="nav-chip">{{ $historialCount }}</span>@endif</a>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if ($reservas->isEmpty())
    <div class="card">
        <div class="empty-state">{{ $tab === 'historial' ? 'No hay reservas en el historial.' : 'No hay reservas próximas.' }}</div>
    </div>
@else
    @foreach ($grouped as $fecha => $diaReservas)
        <div class="card" style="margin-bottom:18px">
            <div class="card-head">
                <div>
                    <div class="card-title" style="text-transform:capitalize">{{ $fecha }}</div>
                    <div class="card-sub">{{ $diaReservas->count() }} reserva(s)</div>
                </div>
            </div>
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            @if (auth()->user()->isAdmin())
                                <th>Cliente</th>
                            @endif
                            <th>Mesa</th>
                            <th>Hora</th>
                            <th>Personas</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diaReservas as $reserva)
                            @php
                                $estadoClasses = match($reserva->estado) {
                                    'pendiente' => 'b-amber',
                                    'confirmada' => 'b-blue',
                                    'completada' => 'b-green',
                                    default => 'b-red',
                                };
                                $estadoLabel = ucfirst($reserva->estado);
                                $avatarClass = 'a' . (($reserva->user_id % 5) + 1);
                                $hora = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $reserva->fecha_hora)->format('H:i');
                            @endphp
                            <tr>
                                @if (auth()->user()->isAdmin())
                                    <td>
                                        <div class="ucell">
                                            <div class="av-sm {{ $avatarClass }}">{{ strtoupper(substr($reserva->user->name, 0, 2)) }}</div>
                                            <div>
                                                <strong>{{ $reserva->user->name }}</strong>
                                                <small>{{ $reserva->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                <td><strong>{{ $reserva->mesa ? 'Mesa ' . $reserva->mesa->numero : '—' }}</strong></td>
                                <td>{{ $hora }}</td>
                                <td>{{ $reserva->numero_personas }}</td>
                                <td><span class="badge {{ $estadoClasses }}"><span class="bdot"></span>{{ $estadoLabel }}</span></td>
                                <td>{{ $reserva->observaciones ?? '—' }}</td>
                                <td>
                                    @if (auth()->user()->isAdmin() || auth()->id() === $reserva->user_id)
                                        <div class="list-item-actions">
                                            @if ($reserva->estado === 'pendiente')
                                                <form action="{{ route('reservas.updateStatus', $reserva->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmar esta reserva?');">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="estado" value="confirmada">
                                                    <button type="submit" class="btn-link" style="color:var(--green-text)">Confirmar</button>
                                                </form>
                                            @endif
                                            @if (!in_array($reserva->estado, ['completada', 'cancelada']))
                                                <form action="{{ route('reservas.updateStatus', $reserva->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="estado" value="cancelada">
                                                    <button type="submit" class="btn-link danger">Cancelar</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('reservas.edit', $reserva->id) }}">Editar</a>
                                            <form action="{{ route('reservas.destroy', $reserva->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta reserva?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-link danger">Eliminar</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
@endsection
