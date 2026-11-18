@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Gestión de Roles</div>
        <div class="page-sub">{{ $roles->count() }} roles definidos</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo Rol</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

@if ($roles->isEmpty())
    <div class="empty-state">No hay roles definidos.</div>
@else
<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Permisos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>
                            <div class="ucell">
                                @php
                                    $hex = ltrim($role->color ?? '#3a7bbd', '#');
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));
                                @endphp
                                <span class="badge" style="background:rgba({{ $r }},{{ $g }},{{ $b }},0.12);color:#{{ $hex }}"><span class="bdot"></span>{{ $role->name }}</span>
                            </div>
                        </td>
                        <td><code>{{ $role->slug }}</code></td>
                        <td>{{ $role->permissions_count }} permisos</td>
                        <td>
                            <div class="list-item-actions">
                                <a href="{{ route('admin.roles.edit', $role->id) }}">Editar</a>
                                @if ($role->slug !== 'super_admin')
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este rol?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-link danger">Eliminar</button>
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
