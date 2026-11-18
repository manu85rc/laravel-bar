<?php

namespace App\Services;

use App\Events\PedidoCambio;
use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PedidoCambioNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Envía a todo el personal (admin, super_admin y staff) la notificación
 * interna y la difusión en tiempo real cuando un pedido se crea o cambia
 * de estado. Se excluye siempre al usuario que realizó la acción.
 */
class PedidoNotificacionService
{
    public function creado(Pedido $pedido, ?int $actorId): void
    {
        $this->notificar($pedido, PedidoCambioNotification::ACCION_CREADO, $actorId);
    }

    public function actualizado(Pedido $pedido, ?int $actorId): void
    {
        $this->notificar($pedido, PedidoCambioNotification::ACCION_ACTUALIZADO, $actorId);
    }

    private function notificar(Pedido $pedido, string $accion, ?int $actorId): void
    {
        try {
            $destinatarios = User::query()
                ->with('pushSubscriptions')
                ->whereHas('role', fn ($q) => $q->whereIn('slug', [
                    User::ROLE_SUPER_ADMIN,
                    User::ROLE_ADMIN,
                    User::ROLE_STAFF,
                ]))
                ->when($actorId, fn ($q) => $q->where('id', '!=', $actorId))
                ->get();

            $actor = $actorId ? User::find($actorId) : null;

            if ($destinatarios->isNotEmpty()) {
                $notificacion = new PedidoCambioNotification($pedido, $accion, $actor?->name);

                Notification::send($destinatarios, $notificacion);

                $datos = $notificacion->toArray($destinatarios->first());

                app(WebPushService::class)->sendToUsers(
                    $destinatarios,
                    (string) $datos['titulo'],
                    (string) $datos['mensaje'],
                    ['url' => $datos['url'] ?? null, 'tag' => 'pedido-'.$pedido->id],
                );
            }

            // Difusión en tiempo real (una sola vez para todo el personal).
            broadcast(new PedidoCambio($pedido, $accion, $actorId));
        } catch (\Throwable $e) {
            // Una notificación nunca debe romper el flujo del pedido.
            Log::warning('No se pudo notificar el cambio de pedido', [
                'pedido_id' => $pedido->id,
                'accion' => $accion,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
