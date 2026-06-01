<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    //
    protected $fillable = [
        'user_id',
        'mesa_id',
        'fecha_hora',
        'numero_personas',
        'observaciones',
        'estado',
    ];

    public function setFechaHoraAttribute($value)
    {
        $this->attributes['fecha_hora'] = Carbon::createFromFormat('d-m-Y H:i', $value);
    }

    public function getFechaHoraAttribute($value)
    {
        if ($value instanceof Carbon) {
            return $value->format('d-m-Y H:i');
        }

        return Carbon::parse($value)->format('d-m-Y H:i');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }
}
