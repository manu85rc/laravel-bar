<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_FACEBOOK = 'facebook';

    public const PROVIDER_APPLE = 'apple';

    protected $fillable = [
        'provider',
        'provider_id',
        'nickname',
        'avatar',
        'token',
        'refresh_token',
        'expires_at',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, [
            self::PROVIDER_GOOGLE,
            self::PROVIDER_FACEBOOK,
            self::PROVIDER_APPLE,
        ], true);
    }

    public function label(): string
    {
        return match ($this->provider) {
            self::PROVIDER_GOOGLE => 'Google',
            self::PROVIDER_FACEBOOK => 'Facebook',
            self::PROVIDER_APPLE => 'Apple',
            default => ucfirst($this->provider),
        };
    }
}
