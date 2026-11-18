<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MercadoPagoSetting;
use Illuminate\Http\Request;

class MercadoPagoSettingController extends Controller
{
    public function edit()
    {
        $settings = MercadoPagoSetting::getInstance();

        return view('admin.mercado_pago.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $boleanos = [
            'mostrar_fecha', 'mostrar_id', 'mostrar_pagador', 'mostrar_estado', 'mostrar_metodo',
            'mostrar_tipo', 'mostrar_descripcion', 'mostrar_monto', 'mostrar_detalle',
        ];

        $request->validate([
            'access_token' => 'nullable|string|max:500',
            'estados' => 'required|array|min:1',
            'estados.*' => 'string|in:'.implode(',', array_keys(MercadoPagoSetting::ESTADOS)),
            'limite' => 'required|integer|in:10,20,50,100',
            ...collect($boleanos)->mapWithKeys(fn ($campo) => [$campo => 'nullable|boolean'])->all(),
        ]);

        $settings = MercadoPagoSetting::getInstance();

        $data = [
            'estados' => $request->input('estados'),
            'limite' => (int) $request->input('limite'),
        ];

        foreach ($boleanos as $campo) {
            $data[$campo] = $request->boolean($campo);
        }

        // Token vacío o enmascarado = no se modifica.
        $token = trim((string) $request->input('access_token', ''));
        if ($token !== '' && ! str_contains($token, '••••')) {
            $data['access_token'] = $token;
        }

        $settings->update($data);

        return redirect()->route('admin.mercado-pago-settings.edit')
            ->with('success', 'Configuración de Mercado Pago actualizada.');
    }
}
