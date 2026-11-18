@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Editar Perfil</div>
        <div class="page-sub">{{ auth()->user()->name }}</div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" name="name" id="name" class="form-input" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>

                    <div class="form-group full" x-data="{ preview: null }">
                        <label class="form-label">Foto de perfil</label>
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="Foto" class="img-preview" style="width:80px;height:80px;border-radius:50%;margin-top:0;margin-bottom:8px">
                            <div class="form-checkbox" style="margin-bottom:8px">
                                <input type="checkbox" name="eliminar_foto" id="eliminar_foto" value="1">
                                <label for="eliminar_foto" style="color:var(--red-text)">Eliminar foto actual</label>
                            </div>
                        @endif
                        <div class="file-input-wrap">
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" @change="preview = URL.createObjectURL($event.target.files[0])">
                            <span class="form-hint">JPG, PNG, GIF, SVG, WebP — máx. 5MB</span>
                        </div>
                        <template x-if="preview">
                            <img :src="preview" alt="Vista previa" class="img-preview" style="width:80px;height:80px;border-radius:50%">
                        </template>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Apariencia</label>
                        <div class="theme-options" x-data="{ theme: '{{ auth()->user()->theme }}' }">
                            <input type="radio" name="theme" value="light" x-model="theme" id="theme-light" style="display:none">
                            <input type="radio" name="theme" value="dark" x-model="theme" id="theme-dark" style="display:none">
                            <input type="radio" name="theme" value="system" x-model="theme" id="theme-system" style="display:none">

                            <button type="button" class="theme-option" :class="{ active: theme === 'light' }" @click="setThemeOption('light')">
                                <i class="fa-solid fa-sun"></i>
                                <span class="to-label">Claro</span>
                            </button>
                            <button type="button" class="theme-option" :class="{ active: theme === 'dark' }" @click="setThemeOption('dark')">
                                <i class="fa-solid fa-moon"></i>
                                <span class="to-label">Oscuro</span>
                            </button>
                            <button type="button" class="theme-option" :class="{ active: theme === 'system' }" @click="setThemeOption('system')">
                                <i class="fa-solid fa-desktop"></i>
                                <span class="to-label">Sistema</span>
                            </button>
                        </div>
                    </div>

                    <style>
                        .theme-options { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px; }
                        .theme-option {
                            display: flex; flex-direction: column; align-items: center; gap: 8px;
                            padding: 18px 24px; border-radius: var(--radius-lg);
                            border: 2px solid var(--border-dim); background: var(--bg-4);
                            color: var(--text-3); cursor: pointer; font-family: inherit;
                            transition: all 0.15s; min-width: 100px; flex: 1;
                        }
                        .theme-option:hover { border-color: var(--border); color: var(--text-2); }
                        .theme-option.active { border-color: var(--gold); background: var(--gold-glow); color: var(--gold); }
                        .theme-option i { font-size: 22px; }
                        .theme-option .to-label { font-size: 13px; font-weight: 500; }
                    </style>
                    <script>
                        function setThemeOption(theme) {
                            document.querySelector('input[name="theme"][value="' + theme + '"]').checked = true;
                            if (typeof setTheme === 'function') setTheme(theme);
                            fetch('{{ route('profile.updateTheme') }}', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ theme: theme })
                            });
                        }
                    </script>
                </div>

                <div class="form-actions">
                    <a href="{{ url('/dashboard') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $user = auth()->user();
        $linked = $user->socialAccounts()->get()->keyBy('provider');
        $availableToLink = collect([
            ['key' => 'google', 'label' => 'Google', 'icon' => 'fa-brands fa-google'],
            ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa-brends fa-facebook-f'],
        ])->filter(fn ($p) => filled(config('services.'.$p['key'].'.client_id')) && ! $linked->has($p['key']));
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Cuentas vinculadas</label>
                <span class="form-hint">Ingresa también con Google o Facebook sin recordar tu contraseña.</span>
            </div>

            @if ($linked->isNotEmpty())
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                    @foreach ($linked as $account)
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--border-dim);border-radius:var(--radius);">
                            <i class="{{ ['google' => 'fa-brands fa-google', 'facebook' => 'fa-brands fa-facebook-f'][$account->provider] ?? 'fa-solid fa-link' }}" style="color:var(--gold);font-size:16px"></i>
                            <div style="flex:1;">
                                <div style="font-size:13px;font-weight:500;">{{ $account->label() }}</div>
                                @if ($account->nickname)
                                    <div style="font-size:11px;color:var(--text-4);">{{ $account->nickname }}</div>
                                @endif
                            </div>
                            @if ($user->canUnlinkSocialAccount())
                                <form action="{{ route('social.unlink', $account) }}" method="POST" onsubmit="return confirm('¿Desvincular {{ $account->label() }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--red-text)">Desvincular</button>
                                </form>
                            @else
                                <span style="font-size:11px;color:var(--text-4);">Única forma de acceso</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($availableToLink->isNotEmpty())
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach ($availableToLink as $provider)
                        <a class="btn btn-sm btn-ghost" href="{{ route('social.redirect', ['provider' => $provider['key']]) }}">
                            <i class="{{ $provider['icon'] }}" style="color:var(--gold);"></i>
                            Vincular {{ $provider['label'] }}
                        </a>
                    @endforeach
                </div>
            @elseif ($linked->isNotEmpty())
                <span class="form-hint">Todas las cuentas sociales disponibles ya están vinculadas.</span>
            @endif
        </div>
    </div>
</div>
@endsection
