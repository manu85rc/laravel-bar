@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Mesas</div>
        <div class="page-sub">{{ $mesas->count() }} mesas registradas</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.mesas.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nueva Mesa</a>
        <a href="{{ route('admin.mesas.plano') }}" class="btn btn-ghost"><i class="fa-solid fa-table-cells"></i> Ver Plano</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

@if (!empty($pisos))
<div class="filter-bar">
    <div class="filter-group">
        <label>Filtrar por piso</label>
        <select class="form-select" onchange="if(this.value) window.location=this.value;">
            <option value="{{ route('admin.mesas.index') }}" {{ !$pisoActual ? 'selected' : '' }}>Todos</option>
            @foreach ($pisos as $p)
                <option value="{{ route('admin.mesas.index', ['piso' => $p]) }}" {{ $p === $pisoActual ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
</div>
@endif

@if ($mesas->isEmpty())
    <div class="empty-state">No hay mesas registradas{{ $pisoActual ? ' en este piso' : '' }}.</div>
@else
<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Capacidad</th>
                    <th>Ubicación</th>
                    <th>Piso</th>
                    <th>Disponible</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mesas as $mesa)
                    <tr>
                        <td><strong>Mesa {{ $mesa->numero }}</strong></td>
                        <td>{{ $mesa->capacidad }} pers.</td>
                        <td>{{ $mesa->ubicacion ?? '—' }}</td>
                        <td>{{ $mesa->piso ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $mesa->disponible ? 'b-green' : 'b-red' }}">
                                <span class="bdot"></span>{{ $mesa->disponible ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <div class="list-item-actions">
                                <a href="{{ route('admin.mesas.edit', $mesa) }}">Editar</a>
                                <form action="{{ route('admin.mesas.destroy', $mesa) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link danger">Eliminar</button>
                                </form>
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
