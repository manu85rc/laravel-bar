@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Gestión de Usuarios</div>
        <div class="page-sub">{{ $users->count() }} usuarios</div>
    </div>
    <div class="btn-row">
        <a href="{{ route('admin.users.create') }}" class="btn btn-gold"><i class="fa-solid fa-plus"></i> Nuevo Usuario</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

@if ($users->isEmpty())
    <div class="empty-state">No hay usuarios registrados.</div>
@else
<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="ucell">
                                @php
                                    $inits = strtoupper(substr($user->name, 0, 1) . substr($user->name, strpos($user->name, ' ') + 1, 1));
                                    if (strlen($inits) < 2) $inits = strtoupper(substr($user->name, 0, 2));
                                @endphp
                                @if ($user->profilePhotoUrl())
                                    <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}" class="av-sm" style="object-fit:cover;">
                                @else
                                    <div class="av-sm a1">{{ $inits }}</div>
                                @endif
                                <strong>{{ $user->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->role)
                                @php
                                    $hex = ltrim($user->role->color ?? '#3a7bbd', '#');
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));
                                @endphp
                                <span class="badge" style="background:rgba({{ $r }},{{ $g }},{{ $b }},0.12);color:#{{ $hex }}"><span class="bdot"></span>{{ $user->role->name }}</span>
                            @else
                                <span class="badge b-gray"><span class="bdot"></span>Sin rol</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="list-item-actions">
                                @if (auth()->user()->isSuperAdmin() && $user->id !== auth()->id())
                                    <form action="{{ route('admin.users.login-as', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-link" title="Entrar como este usuario"><i class="fa-solid fa-right-to-bracket"></i> Entrar como</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.users.edit', $user->id) }}">Editar</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?');">
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
