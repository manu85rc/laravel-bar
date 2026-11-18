<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintQueueJob extends Model
{
    protected $table = 'print_queue';

    protected $fillable = [
        'printer_id',
        'ticket_data',
        'pending',
    ];

    protected $casts = [
        'ticket_data' => 'array',
        'pending' => 'boolean',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }
}
