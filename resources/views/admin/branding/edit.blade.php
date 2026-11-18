@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title">Configuración</div>
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
                            <img src="{{ $branding->logoUrl() }}" alt="Logo actual" class="img-preview" style="margin-top:0;margin-bottom:8px;object-fit:contain">
                        @else
                            <p class="form-hint" style="margin-bottom:6px">No hay logo establecido</p>
                        @endif
                        <div class="file-input-wrap">
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" @change="preview = URL.createObjectURL($event.target.files[0])">
                            <span class="form-hint">JPG, PNG, GIF, SVG, WebP — máx. 5MB</span>
                        </div>
                        <template x-if="preview">
                            <img :src="preview" alt="Vista previa" class="img-preview" style="object-fit:contain">
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

                    <div class="form-group full">
                        <div class="card-section-title" style="margin-bottom:8px;padding-top:8px">Redes Sociales</div>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="social_instagram">
                            <i class="fa-brands fa-instagram"></i>&nbsp; Instagram
                        </label>
                        <input type="text" name="social_instagram" id="social_instagram" class="form-input" value="{{ old('social_instagram', $branding->social_instagram) }}" placeholder="https://instagram.com/tucuenta">
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="social_facebook">
                            <i class="fa-brands fa-facebook-f"></i>&nbsp; Facebook
                        </label>
                        <input type="text" name="social_facebook" id="social_facebook" class="form-input" value="{{ old('social_facebook', $branding->social_facebook) }}" placeholder="https://facebook.com/tupagina">
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="social_whatsapp">
                            <i class="fa-brands fa-whatsapp"></i>&nbsp; WhatsApp
                        </label>
                        <input type="text" name="social_whatsapp" id="social_whatsapp" class="form-input" value="{{ old('social_whatsapp', $branding->social_whatsapp) }}" placeholder="Ej: +54 341 000-0000 o https://wa.me/5493410000000">
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="social_tiktok">
                            <i class="fa-brands fa-tiktok"></i>&nbsp; TikTok
                        </label>
                        <input type="text" name="social_tiktok" id="social_tiktok" class="form-input" value="{{ old('social_tiktok', $branding->social_tiktok) }}" placeholder="https://tiktok.com/@tucuenta">
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="social_twitter">
                            <i class="fa-brands fa-x-twitter"></i>&nbsp; X (Twitter)
                        </label>
                        <input type="text" name="social_twitter" id="social_twitter" class="form-input" value="{{ old('social_twitter', $branding->social_twitter) }}" placeholder="https://x.com/tucuenta">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('dashboard') }}" class="btn-link">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Actualizar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
