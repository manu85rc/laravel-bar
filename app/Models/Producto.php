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
        'foto_path',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function fotoUrl()
    {
        return $this->foto_path ? asset('storage/' . $this->foto_path) : null;
    }
}