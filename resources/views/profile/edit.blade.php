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
</div>
@endsection
