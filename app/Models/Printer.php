<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'nombre',
        'conexion',
        'tipo',
        'papel',
        'ruta_dispositivo',
        'baud_rate',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'baud_rate' => 'integer',
        ];
    }
}
