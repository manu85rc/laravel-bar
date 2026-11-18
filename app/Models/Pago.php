<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mesa_id',
        'user_id',
        'metodo_pago',
        'modo',
        'partes',
        'monto',
    ];

    protected $casts = [
        'partes' => 'integer',
        'monto' => 'decimal:2',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * IDs de líneas (pedido_producto) cubiertas por este pago.
     *
     * @return array<int, int>
     */
    public function lineasIds(): array
    {
        return \DB::table('pago_producto')
            ->where('pago_id', $this->id)
            ->pluck('pedido_producto_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
