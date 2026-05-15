<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branding extends Model
{
    protected $fillable = [
        'app_name',
        'logo_path',
        'favicon_path',
    ];

    public static function getInstance()
    {
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
