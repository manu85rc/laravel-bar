@extends('layouts.guest')

@section('left-headline')
    Restablece tu contraseña
@endsection

@section('left-subtitle')
    Ingresa tu nueva contraseña para acceder a tu cuenta.
@endsection

@section('content')
<div class="login-title">Restablecer contraseña</div>
<p class="login-sub">Elige una contraseña segura para tu cuenta.</p>

<form method="POST" action="{{ route('password.update') }}" id="resetForm" novalidate>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="field @error('email') error @enderror" id="field-email">
        <label class="field-label" for="email">Correo electrónico</label>
        <div class="input-wrap">
            <i class="fa-regular fa-envelope input-icon"></i>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" placeholder="manager@copperstill.com" autocomplete="email"/>
        </div>
        <div class="field-err">@error('email') {{ $message }} @enderror</div>
    </div>

    <div class="field @error('password') error @enderror" id="field-password">
        <label class="field-label" for="password">Nueva contraseña</label>
        <div class="input-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="new-password"/>
            <button type="button" class="pw-toggle" onclick="togglePassword()" aria-label="Mostrar contraseña">
                <i class="fa-regular fa-eye" id="pw-icon"></i>
            </button>
        </div>
        <div class="field-err">@error('password') {{ $message }} @else Mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos. @enderror</div>
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
        <span class="btn-text">Restablecer contraseña</span>
        <i class="fa-solid fa-arrow-right" id="btn-arrow"></i>
    </button>
</form>

<div class="login-footer">
    <a href="{{ route('login') }}">Volver al inicio de sesión</a> &nbsp;·&nbsp; <a href="#">Política de privacidad</a>
</div>
@endsection

@push('scripts')
<script>
function validate() {
    let ok = true;
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;
    const pass2 = document.getElementById('password_confirmation').value;
    document.getElementById('field-email').classList.remove('error');
    document.getElementById('field-password').classList.remove('error');
    document.getElementById('field-password-confirm').classList.remove('error');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('field-email').classList.add('error'); ok = false;
    }
    if (pass.length < 8) {
        document.getElementById('field-password').classList.add('error'); ok = false;
    }
    if (pass !== pass2) {
        document.getElementById('field-password-confirm').classList.add('error'); ok = false;
    }
    return ok;
}

document.getElementById('resetForm').addEventListener('submit', function(e) {
    if (!validate()) { e.preventDefault(); return; }
    document.getElementById('submitBtn').classList.add('loading');
    document.getElementById('btn-arrow').style.display = 'none';
});
</script>
@endpush
