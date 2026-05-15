<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'parent_id',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
