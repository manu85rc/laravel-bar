@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Branding</div>
        <div class="page-sub">Personaliza el nombre y logo de tu aplicación</div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="content-max">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="app_name">Nombre de la Aplicación</label>
                        <input type="text" name="app_name" id="app_name" class="form-input" value="{{ old('app_name', $branding->app_name) }}" required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="tagline">Tagline / Subtítulo</label>
                        <input type="text" name="tagline" id="tagline" class="form-input" value="{{ old('tagline', $branding->tagline) }}" placeholder="Ej: Bar &amp; Cocina">
                    </div>

                    <div class="form-group full" x-data="{ preview: null }">
                        <label class="form-label">Logo</label>
                        @if ($branding->logoUrl())
                            <img src="{{ $branding->logoUrl() }}" alt="Logo actual" class="img-preview" style="margin-top:0;margin-bottom:8px;width:auto;max-width:200px;height:auto;max-height:100px">
                        @else
                            <p class="form-hint" style="margin-bottom:6px">No hay logo establecido</p>
                        @endif
                        <div class="file-input-wrap">
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" @change="preview = URL.createObjectURL($event.target.files[0])">
                            <span class="form-hint">JPG, PNG, GIF, SVG, WebP — máx. 5MB</span>
                        </div>
                        <template x-if="preview">
                            <img :src="preview" alt="Vista previa" class="img-preview" style="width:auto;max-width:200px;height:auto;max-height:100px">
                        </template>
                    </div>

                    <div class="form-group full" x-data="{ preview: null }">
                        <label class="form-label">Favicon</label>
                        @if ($branding->faviconUrl())
                            <img src="{{ $branding->faviconUrl() }}" alt="Favicon actual" class="img-preview img-preview-sm" style="margin-top:0;margin-bottom:8px">
                        @else
                            <p class="form-hint" style="margin-bottom:6px">No hay favicon establecido</p>
                        @endif
                        <div class="file-input-wrap">
                            <input type="file" name="favicon" accept="image/x-icon,image/jpeg,image/png,image/gif,image/svg+xml,image/webp" @change="preview = URL.createObjectURL($event.target.files[0])">
                            <span class="form-hint">ICO, PNG, JPG, GIF, SVG, WebP — máx. 5MB</span>
                        </div>
                        <template x-if="preview">
                            <img :src="preview" alt="Vista previa" class="img-preview img-preview-sm">
                        </template>
                    </div>

                    <div class="form-group full">
                        <div class="card-section-title" style="margin-bottom:8px;padding-top:8px">Información del Restaurante</div>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="address">Dirección</label>
                        <input type="text" name="address" id="address" class="form-input" value="{{ old('address', $branding->address) }}" placeholder="Ej: San Martín 1234 · Rosario">
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone', $branding->phone) }}" placeholder="Ej: +54 341 000-0000">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('dashboard') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Branding</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
