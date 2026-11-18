<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSync extends Model
{
    protected $table = 'offline_sync';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'type',
        'user_id',
        'server_id',
        'payload',
        'estado',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
