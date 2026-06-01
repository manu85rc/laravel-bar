@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Mis Reservas</div>
        <div class="page-sub">{{ $reservas->count() }} reservas</div>
    </div>
    @if (auth()->user()->isAdmin())
        <div class="btn-row">
            <a href="{{ route('reservas.create') }}" class="btn btn-gold"><i class="fa-regular fa-calendar-plus"></i> Nueva Reserva</a>
        </div>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if ($reservas->isEmpty())
    <div class="empty-state">No tienes reservas.</div>
@else
<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mesa</th>
                    <th>Fecha y Hora</th>
                    <th>Personas</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservas as $reserva)
                    @php
                        $estadoClasses = match($reserva->estado) {
                            'pendiente' => 'b-amber',
                            'confirmada' => 'b-blue',
                            'completada' => 'b-green',
                            default => 'b-red',
                        };
                        $estadoLabel = ucfirst($reserva->estado);
                    @endphp
                    <tr>
                        <td><strong>{{ $reserva->mesa ? 'Mesa ' . $reserva->mesa->numero : '—' }}</strong></td>
                        <td>{{ $reserva->fecha_hora }}</td>
                        <td>{{ $reserva->numero_personas }}</td>
                        <td><span class="badge {{ $estadoClasses }}"><span class="bdot"></span>{{ $estadoLabel }}</span></td>
                        <td>{{ $reserva->observaciones ?? '—' }}</td>
                        <td>
                            @if (auth()->user()->isAdmin() || auth()->id() === $reserva->user_id)
                                <div class="list-item-actions">
                                    <a href="{{ route('reservas.edit', $reserva->id) }}">Editar</a>
                                    <form action="{{ route('reservas.destroy', $reserva->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
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
@endif
@endsection
