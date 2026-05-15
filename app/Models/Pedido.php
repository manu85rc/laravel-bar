<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mesa_id',
        'user_id',
        'estado',
        'total',
    ];

    /**
     * Get the mesa that owns the pedido.
     */
    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    /**
     * Get the user (camarero) that took the pedido.
     */
    public function camarero()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the productos in the pedido.
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->withPivot('cantidad', 'precio_unitario')
            ->withTimestamps();
    }
}