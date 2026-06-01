@extends('layouts.guest')

@section('left-headline')
    Recupera tu acceso
@endsection

@section('left-subtitle')
    Te enviaremos un enlace para restablecer tu contraseña.
@endsection

@section('content')
<div class="login-title">Restablecer contraseña</div>
<p class="login-sub">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>

<form method="POST" action="{{ route('password.email') }}" id="resetForm" novalidate>
    @csrf

    <div class="field @error('email') error @enderror" id="field-email">
        <label class="field-label" for="email">Correo electrónico</label>
        <div class="input-wrap">
            <i class="fa-regular fa-envelope input-icon"></i>
            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="manager@copperstill.com" autocomplete="email"/>
        </div>
        <div class="field-err">@error('email') {{ $message }} @else Ingresa un correo válido. @enderror</div>
    </div>

    <button class="btn-submit" type="submit" id="submitBtn">
        <div class="spinner"></div>
        <span class="btn-text">Enviar enlace</span>
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
    document.getElementById('field-email').classList.remove('error');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('field-email').classList.add('error'); ok = false;
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
