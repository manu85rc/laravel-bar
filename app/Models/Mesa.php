<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero',
        'zona_id',
        'disponible',
        'pos_x',
        'pos_y',
        'forma',
        'ancho',
        'alto',
        'color',
    ];

    /**
     * Get the pedidos for the mesa.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
