@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Nuevo Rol</div>
        <div class="page-sub">Define un nuevo rol con sus permisos</div>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required placeholder="Ej: Gerente">
                        @if ($errors->has('name'))<span class="form-error">{{ $errors->first('name') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="slug">Slug (identificador)</label>
                        <input type="text" name="slug" id="slug" class="form-input" value="{{ old('slug') }}" required placeholder="Ej: gerente" pattern="[a-zA-Z0-9_-]+">
                        <span class="form-hint">Solo letras, números, guiones bajos y guiones. Sin espacios.</span>
                        @if ($errors->has('slug'))<span class="form-error">{{ $errors->first('slug') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Color del rol</label>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:4px">
                            <div id="swatches" style="display:flex;gap:6px;flex-wrap:wrap">
                                @foreach (['#2d8f5e','#3a7bbd','#c8922a','#c0392b','#7c5cc8','#0891b2','#c2410c','#9333ea','#0d9488','#be185d'] as $swatch)
                                    <button type="button" class="color-swatch" data-color="{{ $swatch }}" style="width:28px;height:28px;border-radius:50%;border:2px solid transparent;background:{{ $swatch }};cursor:pointer;transition:all .15s;padding:0;" tabindex="-1"></button>
                                @endforeach
                            </div>
                            <input type="color" name="color" id="color" value="{{ old('color', $defaultColor) }}" style="width:36px;height:36px;border:none;padding:0;background:transparent;cursor:pointer;">
                            <span id="color-hex" style="font-size:12px;color:var(--text-3);font-family:monospace">{{ old('color', $defaultColor) }}</span>
                        </div>
                        @if ($errors->has('color'))<span class="form-error">{{ $errors->first('color') }}</span>@endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Permisos</label>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;margin-top:4px">
                            @foreach ($permissions as $permission)
                                <div class="form-checkbox">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <label for="perm_{{ $permission->id }}">{{ $permission->name }} <small style="color:var(--text-4)">({{ $permission->slug }})</small></label>
                                </div>
                            @endforeach
                        </div>
                        @if ($errors->has('permissions'))<span class="form-error">{{ $errors->first('permissions') }}</span>@endif
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.roles.index') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Crear Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const colorInput = document.getElementById('color');
    const hexLabel = document.getElementById('color-hex');
    const swatches = document.querySelectorAll('.color-swatch');
    const currentVal = colorInput.value;

    swatches.forEach(s => {
        if (s.dataset.color === currentVal) s.style.boxShadow = '0 0 0 2px var(--bg-2), 0 0 0 4px ' + s.dataset.color;
        s.addEventListener('click', () => {
            colorInput.value = s.dataset.color;
            hexLabel.textContent = s.dataset.color;
            swatches.forEach(x => x.style.boxShadow = 'none');
            s.style.boxShadow = '0 0 0 2px var(--bg-2), 0 0 0 4px ' + s.dataset.color;
        });
    });

    colorInput.addEventListener('input', () => {
        hexLabel.textContent = colorInput.value;
        swatches.forEach(x => x.style.boxShadow = 'none');
    });
});
</script>

<style>
.color-swatch:hover { transform: scale(1.15); }
</style>
@endsection
