@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Impresoras</div>
        <div class="page-sub">{{ $printers->count() }} impresoras configuradas</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.printers.terminal') }}" class="btn btn-ghost"><i class="fa-solid fa-desktop"></i> Terminal</a>
        <a href="{{ route('admin.printers.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nueva Impresora</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Conexión</th>
                    <th>Tipo</th>
                    <th>Papel</th>
                    <th>Dispositivo</th>
                    <th>Baud Rate</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($printers as $printer)
                    <tr>
                        <td>
                            <div class="ucell">
                                <div class="av-sm a3"><i class="fa-solid fa-print"></i></div>
                                <div>
                                    <strong>{{ $printer->nombre }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($printer->conexion === 'bluetooth')
                                <span class="badge b-blue"><i class="fa-brands fa-bluetooth" style="margin-right:4px"></i> Bluetooth</span>
                            @else
                                <span class="badge b-gray"><i class="fa-solid fa-usb" style="margin-right:4px"></i> USB</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $tipoLabel = match($printer->tipo) {
                                    'cocina' => 'Cocina',
                                    'bebidas' => 'Bebidas',
                                    'ambas' => 'Ambas',
                                    'factura' => 'Factura',
                                    default => $printer->tipo,
                                };
                                $tipoBadge = match($printer->tipo) {
                                    'cocina' => 'b-amber',
                                    'bebidas' => 'b-blue',
                                    'ambas' => 'b-green',
                                    'factura' => 'b-purple',
                                    default => 'b-gray',
                                };
                            @endphp
                            <span class="badge {{ $tipoBadge }}">{{ $tipoLabel }}</span>
                        </td>
                        <td><code style="font-size:12px;color:var(--text-2)">{{ $printer->papel ?? '80mm' }}</code></td>
                        <td><code style="font-size:12px;color:var(--text-2)">{{ $printer->ruta_dispositivo }}</code></td>
                        <td><code style="font-size:12px;color:var(--text-2)">{{ $printer->baud_rate ?? '9600' }}</code></td>
                        <td>
                            <span class="badge {{ $printer->activo ? 'b-green' : 'b-red' }}">
                                <span class="bdot"></span>{{ $printer->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td>
                            <div class="list-item-actions">
                                <a href="{{ route('admin.printers.edit', $printer->id) }}">Editar</a>
                                <form action="{{ route('admin.printers.destroy', $printer->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta impresora?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No hay impresoras configuradas. <a href="{{ route('admin.printers.create') }}" style="color:var(--gold)">Crear una</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
