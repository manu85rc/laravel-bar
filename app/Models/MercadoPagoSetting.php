<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoPagoSetting extends Model
{
    protected $table = 'mercado_pago_settings';

    protected $fillable = [
        'access_token',
        'mostrar_fecha',
        'mostrar_id',
        'mostrar_pagador',
        'mostrar_estado',
        'mostrar_metodo',
        'mostrar_tipo',
        'mostrar_descripcion',
        'mostrar_monto',
        'mostrar_detalle',
        'estados',
        'limite',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'mostrar_fecha' => 'boolean',
        'mostrar_id' => 'boolean',
        'mostrar_pagador' => 'boolean',
        'mostrar_estado' => 'boolean',
        'mostrar_metodo' => 'boolean',
        'mostrar_tipo' => 'boolean',
        'mostrar_descripcion' => 'boolean',
        'mostrar_monto' => 'boolean',
        'mostrar_detalle' => 'boolean',
        'estados' => 'array',
        'limite' => 'integer',
    ];

    /** Estados posibles de un pago en Mercado Pago (etiquetas en español). */
    public const ESTADOS = [
        'approved' => 'Aprobado',
        'pending' => 'Pendiente',
        'in_process' => 'En proceso',
        'authorized' => 'Autorizado',
        'in_mediation' => 'En mediación',
        'rejected' => 'Rechazado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Devuelto',
        'charged_back' => 'Contracargo',
    ];

    public static function getInstance(): self
    {
        return self::firstOrCreate([], [
            'estados' => array_keys(self::ESTADOS),
            'limite' => 50,
        ]);
    }
}
