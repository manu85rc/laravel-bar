<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $fillable = ['nombre', 'orden'];

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }
}
