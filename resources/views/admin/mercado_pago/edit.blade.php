@extends('layouts.admin')

@section('content')
<div class="page-top">
    <div>
        <div class="page-title"><x-mp-icon /> Mercado Pago</div>
        <div class="page-sub">Configura qué movimientos se muestran en la pestaña de Pedidos</div>
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
            <form action="{{ route('admin.mercado-pago-settings.update') }}" method="POST">
                @csrf @method('PUT')

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label" for="access_token">Access Token</label>
                        @if ($settings->access_token)
                            <input type="password" name="access_token" id="access_token" class="form-input" value="{{ old('access_token', '••••••••••••••••') }}" placeholder="Token guardado. Dejar vacío o sin modificar para conservarlo." autocomplete="off">
                            <span class="form-hint">Guardado y cifrado. Pega un token nuevo de <a href="https://www.mercadopago.com/developers/panel/app" target="_blank" rel="noopener">Tu cuenta → Credenciales</a> para reemplazarlo.</span>
                        @else
                            <input type="text" name="access_token" id="access_token" class="form-input" value="{{ old('access_token') }}" placeholder="APP_USR-..." autocomplete="off" required>
                            <span class="form-hint">Lo encontrás en el panel de desarrolladores de Mercado Pago. Se guarda cifrado y solo vos (super admin) podés verlo/editarlo.</span>
                        @endif
                    </div>

                    <div class="form-group full">
                        <div class="card-section-title" style="margin-bottom:8px;padding-top:8px">Columnas a mostrar</div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
                            @foreach ([
                                'mostrar_fecha' => 'Fecha',
                                'mostrar_id' => 'ID de pago',
                                'mostrar_pagador' => 'Pagador (quién pagó)',
                                'mostrar_estado' => 'Estado',
                                'mostrar_metodo' => 'Método',
                                'mostrar_tipo' => 'Tipo (ingreso/egreso)',
                                'mostrar_descripcion' => 'Descripción',
                                'mostrar_monto' => 'Monto',
                                'mostrar_detalle' => 'Link a detalle',
                            ] as $campo => $etiqueta)
                                <div class="form-checkbox">
                                    <input type="hidden" name="{{ $campo }}" value="0">
                                    <input type="checkbox" name="{{ $campo }}" value="1" id="{{ $campo }}" {{ old($campo, $settings->{$campo}) ? 'checked' : '' }}>
                                    <label for="{{ $campo }}">{{ $etiqueta }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group full">
                        <div class="card-section-title" style="margin-bottom:8px;padding-top:8px">Estados a incluir</div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
                            @foreach (\App\Models\MercadoPagoSetting::ESTADOS as $slug => $etiqueta)
                                <div class="form-checkbox">
                                    <input type="checkbox" name="estados[]" value="{{ $slug }}" id="estado_{{ $slug }}" {{ in_array($slug, old('estados', $settings->estados ?? []), true) ? 'checked' : '' }}>
                                    <label for="estado_{{ $slug }}">{{ $etiqueta }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group full">
                        <label class="form-label" for="limite">Cantidad de movimientos</label>
                        <select name="limite" id="limite" class="form-input">
                            @foreach ([10, 20, 50, 100] as $n)
                                <option value="{{ $n }}" {{ (int) old('limite', $settings->limite) === $n ? 'selected' : '' }}>Últimos {{ $n }} movimientos</option>
                            @endforeach
                        </select>
                        <span class="form-hint">La lista se actualiza como máximo una vez por minuto.</span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('pedidos.index', ['tab' => 'mercadopago']) }}" class="btn-link">Ver listado</a>
                    <button type="submit" class="btn btn-gold">Guardar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
