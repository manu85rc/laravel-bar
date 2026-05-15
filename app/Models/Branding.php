<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Branding extends Model
{
    protected $table = 'branding';

    protected $fillable = [
        'app_name',
        'logo_path',
        'favicon_path',
    ];

    public static function getInstance()
    {
        // Log the table name for debugging
        $table = (new static)->getTable();
        Log::info('Branding model using table: ' . $table);
        
        return self::firstOrCreate([], [
            'app_name' => 'Mi Bar',
            'logo_path' => null,
            'favicon_path' => null,
        ]);
    }

    public function logoUrl()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function faviconUrl()
    {
        return $this->favicon_path ? asset('storage/' . $this->favicon_path) : null;
    }
}