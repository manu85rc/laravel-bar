<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'theme',
        'role_id',
    ];

    public function profilePhotoUrl()
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_ADMIN = 'admin';

    const ROLE_STAFF = 'staff';

    const ROLE_USER = 'user';

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Notificaciones internas pendientes de leer (campana del header).
     *
     * La relación notifications() y todo lo de read_at (readAt(),
     * markAsRead(), unreadNotifications...) ya lo aporta el trait
     * Notifiable, así que aquí sólo el contador.
     */
    public function notificacionesNoLeidas(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function hasPassword(): bool
    {
        return filled($this->password);
    }

    public function linkedProvider(string $provider): bool
    {
        return $this->socialAccounts()->where('provider', $provider)->exists();
    }

    public function canUnlinkSocialAccount(): bool
    {
        return $this->hasPassword() || $this->socialAccounts()->count() > 1;
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isStaffRole(): bool
    {
        return $this->hasRole(self::ROLE_STAFF);
    }

    public function isUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    public function isStaff(): bool
    {
        return $this->role && in_array($this->role->slug, [
            self::ROLE_ADMIN,
            self::ROLE_SUPER_ADMIN,
            self::ROLE_STAFF,
        ]);
    }

    public function puedeTomarPedidos(): bool
    {
        return $this->isSuperAdmin() || $this->hasPermission('admin.pedidos');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role && $this->role->hasPermission($permissionSlug);
    }

    /**
     * Primera ruta de panel accesible para el usuario, en orden de prioridad.
     *
     * Devuelve null cuando no tiene acceso a ninguna, para que quien llame
     * decida el fallback (normalmente la home pública). El parametro
     * $exceptPermission permite excluir el permiso que acaba de fallar y
     * asi evitar bucles de redireccion.
     */
    public function panelHomeRoute(?string $exceptPermission = null): ?string
    {
        $candidates = [
            'admin.dashboard' => 'dashboard',
            'admin.pedidos' => 'pedidos.index',
            'admin.reservas' => 'reservas.index',
        ];

        foreach ($candidates as $permission => $route) {
            if ($permission !== $exceptPermission && $this->hasPermission($permission)) {
                return $route;
            }
        }

        return null;
    }
}
