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
        "numero",
        "capacidad",
        "ubicacion",
        "disponible",
    ];

    /**
     * Get the pedidos for the mesa.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
