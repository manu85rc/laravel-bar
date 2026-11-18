<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PedidoListoNotification;
use App\Services\NotificacionService;
use App\Services\PedidoNotificacionService;
use App\Services\WebPushService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Al cambiar el estado de un pedido se avisa a todo el personal
 * (admin, super_admin y staff) y, si queda 'listo', además al mesero dueño.
 */
class PedidoObserver
{
    public function updated(Pedido $pedido): void
    {
        if (! $pedido->wasChanged('estado')) {
            return;
        }

        // Aviso general a todo el personal (excepto a quien hizo el cambio).
        app(PedidoNotificacionService::class)->actualizado($pedido, auth()->id());

        if ($pedido->estado !== 'listo') {
            return;
        }

        $destinatario = $this->destinatario($pedido);

        if (! $destinatario) {
            return;
        }

        try {
            $notificacion = new PedidoListoNotification($pedido);

            Notification::send($destinatario, $notificacion);

            $datos = $notificacion->toArray($destinatario);

            app(WebPushService::class)->sendToUser(
                $destinatario,
                (string) $datos['titulo'],
                (string) $datos['mensaje'],
                ['url' => $datos['url'] ?? null, 'tag' => 'pedido-'.$pedido->id],
            );
        } catch (\Throwable $e) {
            // Una notificación nunca debe romper el cambio de estado del pedido.
            Log::warning('No se pudo enviar la notificación de pedido listo', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Si el pedido se elimina, sus notificaciones quedan huérfanas
     * (apuntan a un pedido inexistente), así que se limpian.
     */
    public function deleted(Pedido $pedido): void
    {
        try {
            app(NotificacionService::class)->limpiarDelPedido((int) $pedido->id);
        } catch (\Throwable $e) {
            Log::warning('No se pudieron limpiar las notificaciones del pedido eliminado', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * El aviso va al mesero que tomó el pedido (pedidos.user_id).
     */
    private function destinatario(Pedido $pedido): ?User
    {
        if (! $pedido->user_id) {
            return null;
        }

        return $pedido->relationLoaded('staff') && $pedido->staff
            ? $pedido->staff
            : User::find($pedido->user_id);
    }
}
