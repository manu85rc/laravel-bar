@extends('layouts.guest')

@section('left-headline')
    Bienvenido de nuevo a <em>The Still</em>
@endsection

@section('left-subtitle')
    Tu venue de un vistazo — reservas, cuentas, inventario y personal en un solo lugar.
@endsection

@section('content')
<div class="login-title">Iniciar sesión</div>
<p class="login-sub">Ingresa tus credenciales para acceder al panel de administración.</p>

<form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
    @csrf

    <div class="field @error('email') error @enderror" id="field-email">
        <label class="field-label" for="email">Correo electrónico</label>
        <div class="input-wrap">
            <i class="fa-regular fa-envelope input-icon"></i>
            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="manager@copperstill.com" autocomplete="email"/>
        </div>
        <div class="field-err">@error('email') {{ $message }} @else Ingresa un correo válido. @enderror</div>
    </div>

    <div class="field @error('password') error @enderror" id="field-password">
        <label class="field-label" for="password">Contraseña</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password"/>
            <button type="button" class="pw-toggle" onclick="togglePassword()" aria-label="Mostrar contraseña">
                <i class="fa-regular fa-eye" id="pw-icon"></i>
            </button>
        </div>
        <div class="field-err">@error('password') {{ $message }} @else La contraseña debe tener al menos 8 caracteres. @enderror</div>
    </div>

    <div class="form-meta">
        <label class="checkbox-label">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}/>
            Recuérdame
        </label>
        <a href="{{ route('password.request') }}" class="forgot">¿Olvidaste tu contraseña?</a>
    </div>

    <button class="btn-submit" type="submit" id="submitBtn">
        <div class="spinner"></div>
        <span class="btn-text">Iniciar sesión</span>
        <i class="fa-solid fa-arrow-right" id="btn-arrow"></i>
    </button>
</form>

<div class="divider">o continúa con</div>

<button type="button" class="btn-sso" onclick="showToast('SSO no configurado aún.', false)">
    <i class="fa-solid fa-building" style="color:var(--gold);font-size:15px"></i>
    Inicio de sesión único (SSO)
</button>

<div class="login-footer">
    ¿Necesitas acceso? <a href="#">Contacta a tu administrador</a> &nbsp;·&nbsp; <a href="#">Política de privacidad</a>
</div>
@endsection

@push('scripts')
<script>
function validate() {
    let ok = true;
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;
    document.getElementById('field-email').classList.remove('error');
    document.getElementById('field-password').classList.remove('error');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('field-email').classList.add('error'); ok = false;
    }
    if (pass.length < 8) {
        document.getElementById('field-password').classList.add('error'); ok = false;
    }
    return ok;
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (!validate()) { e.preventDefault(); return; }
    document.getElementById('submitBtn').classList.add('loading');
    document.getElementById('btn-arrow').style.display = 'none';
});
</script>
@endpush
