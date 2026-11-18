<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PedidoCambioNotification;
use App\Notifications\PedidoListoNotification;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Limpieza de la tabla `notifications`:
 * - al borrar un usuario (Admin\AdminController::destroy)
 * - al borrar un pedido (PedidoObserver::deleted)
 * - de forma periódica con `php artisan notificaciones:limpiar`
 */
class NotificacionService
{
    /**
     * Borra todas las notificaciones de un usuario concreto.
     */
    public function limpiarDelUsuario(int $userId): int
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->delete();
    }

    /**
     * Borra las notificaciones de un pedido concreto.
     *
     * El `pedido_id` vive dentro del campo JSON `data`, así que se filtran
     * en PHP para no depender del soporte JSON de MySQL o SQLite.
     */
    public function limpiarDelPedido(int $pedidoId): int
    {
        $aBorrar = DatabaseNotification::query()
            ->whereIn('type', [PedidoListoNotification::class, PedidoCambioNotification::class])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get()
            ->filter(fn ($notificacion) => (int) data_get($notificacion->data, 'pedido_id') === $pedidoId)
            ->pluck('id');

        if ($aBorrar->isEmpty()) {
            return 0;
        }

        return DatabaseNotification::query()->whereKey($aBorrar->all())->delete();
    }

    /**
     * Purga periódica. Devuelve los contadores por tipo de limpieza.
     *
     * @return array{leidas:int, no_leidas:int, huerfanas:int, pedidos:int}
     */
    public function purgar(int $dias = 30): array
    {
        $dias = max(1, $dias);

        $leidas = DatabaseNotification::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($dias))
            ->delete();

        // Las no leídas se conservan el doble de tiempo por si el usuario
        // estuvo días sin conectarse.
        $noLeidas = DatabaseNotification::query()
            ->whereNull('read_at')
            ->where('created_at', '<', now()->subDays($dias * 2))
            ->delete();

        $idsUsuarios = User::query()->pluck('id')->all();

        $huerfanas = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->whereNotIn('notifiable_id', $idsUsuarios ?: [0])
            ->delete();

        $pedidos = 0;
        $idsPedidos = Pedido::query()->pluck('id')->all();

        $notificacionesDePedido = DatabaseNotification::query()
            ->whereIn('type', [PedidoListoNotification::class, PedidoCambioNotification::class])
            ->where('created_at', '<', now()->subDays($dias))
            ->limit(1000)
            ->get();

        $aBorrar = $notificacionesDePedido
            ->filter(fn ($notificacion) => ! in_array((int) data_get($notificacion->data, 'pedido_id'), $idsPedidos ?: [0], true))
            ->pluck('id');

        if ($aBorrar->isNotEmpty()) {
            $pedidos = DatabaseNotification::query()->whereKey($aBorrar->all())->delete();
        }

        return [
            'leidas' => $leidas,
            'no_leidas' => $noLeidas,
            'huerfanas' => $huerfanas,
            'pedidos' => $pedidos,
        ];
    }
}
