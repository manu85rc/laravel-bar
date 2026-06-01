@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Usuario</div>
        <div class="page-sub">{{ $user->name }}</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @if ($errors->has('name'))<span class="form-error">{{ $errors->first('name') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @if ($errors->has('email'))<span class="form-error">{{ $errors->first('email') }}</span>@endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña <span class="form-hint" style="display:inline">(dejar en blanco para no cambiar)</span></label>
                        <input type="password" name="password" id="password" class="form-input" autocomplete="current-password">
                        @if ($errors->has('password'))<span class="form-error">{{ $errors->first('password') }}</span>@endif
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" autocomplete="current-password">
                        @if ($errors->has('password_confirmation'))<span class="form-error">{{ $errors->first('password_confirmation') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="role">Rol</label>
                        <select name="role" id="role" class="form-select" required>
                            @if (Auth::user()->isSuperAdmin())
                                <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Administrador</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuario</option>
                            @endif
                            <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @if ($errors->has('role'))<span class="form-error">{{ $errors->first('role') }}</span>@endif
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
