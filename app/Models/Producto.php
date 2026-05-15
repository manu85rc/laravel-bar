<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria_id',
        'disponible',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
