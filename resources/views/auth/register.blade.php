@extends('layouts.guest')

@section('left-headline')
    Únete a <em>The Still</em>
@endsection

@section('left-subtitle')
    Crea tu cuenta y empieza a gestionar tu venue desde un solo lugar.
@endsection

@section('content')
<div class="login-title">Crear cuenta</div>
<p class="login-sub">Regístrate para acceder al panel de administración.</p>

<form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
    @csrf

    <div class="field @error('name') error @enderror" id="field-name">
        <label class="field-label" for="name">Nombre</label>
        <div class="input-wrap">
            <i class="fa-regular fa-user input-icon"></i>
            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Tu nombre" autocomplete="name"/>
        </div>
        <div class="field-err">@error('name') {{ $message }} @else El nombre es obligatorio. @enderror</div>
    </div>

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
            <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="new-password"/>
            <button type="button" class="pw-toggle" onclick="togglePassword()" aria-label="Mostrar contraseña">
                <i class="fa-regular fa-eye" id="pw-icon"></i>
            </button>
        </div>
        <div class="field-err">@error('password') {{ $message }} @else Mínimo 8 caracteres. @enderror</div>
    </div>

    <div class="field @error('password_confirmation') error @enderror" id="field-password-confirm">
        <label class="field-label" for="password_confirmation">Confirmar contraseña</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" autocomplete="new-password"/>
        </div>
        <div class="field-err">@error('password_confirmation') {{ $message }} @enderror</div>
    </div>

    <button class="btn-submit" type="submit" id="submitBtn">
        <div class="spinner"></div>
        <span class="btn-text">Crear cuenta</span>
        <i class="fa-solid fa-arrow-right" id="btn-arrow"></i>
    </button>
</form>

<div class="divider">o continúa con</div>

<button type="button" class="btn-sso" onclick="showToast('SSO no configurado aún.', false)">
    <i class="fa-solid fa-building" style="color:var(--gold);font-size:15px"></i>
    Inicio de sesión único (SSO)
</button>

<div class="login-footer">
    ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a> &nbsp;·&nbsp; <a href="#">Política de privacidad</a>
</div>
@endsection

@push('scripts')
<script>
function validate() {
    let ok = true;
    const name  = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;
    const conf  = document.getElementById('password_confirmation').value;
    document.getElementById('field-name').classList.remove('error');
    document.getElementById('field-email').classList.remove('error');
    document.getElementById('field-password').classList.remove('error');
    document.getElementById('field-password-confirm').classList.remove('error');
    if (name.length < 2) {
        document.getElementById('field-name').classList.add('error'); ok = false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('field-email').classList.add('error'); ok = false;
    }
    if (pass.length < 8) {
        document.getElementById('field-password').classList.add('error'); ok = false;
    }
    if (pass !== conf) {
        document.getElementById('field-password-confirm').classList.add('error'); ok = false;
    }
    return ok;
}

document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (!validate()) { e.preventDefault(); return; }
    document.getElementById('submitBtn').classList.add('loading');
    document.getElementById('btn-arrow').style.display = 'none';
});
</script>
@endpush
