<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificación interna (canal database) que avisa al mesero que su pedido
 * pasó a estado 'listo' y ya puede servirse.
 *
 * El payload se guarda en español para que el frontend (campana) lo muestre
 * tal cual, sin tener que traducir nada en el navegador.
 */
class PedidoListoNotification extends Notification
{
    use Queueable;

    public function __construct(public Pedido $pedido) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $mesa = $this->pedido->relationLoaded('mesa')
            ? $this->pedido->mesa
            : $this->pedido->mesa()->first();

        $productos = $this->pedido->relationLoaded('productos')
            ? $this->pedido->productos->count()
            : $this->pedido->productos()->count();

        $etiquetaMesa = $mesa ? 'Mesa '.$mesa->numero : 'Pedido sin mesa';

        return [
            'tipo' => 'pedido_listo',
            'icono' => 'fa-solid fa-bell-concierge',
            'color' => 'verde',
            'titulo' => 'Pedido listo',
            'mensaje' => $etiquetaMesa.': el pedido #'.$this->pedido->id.' ya está listo para servir.',
            'detalle' => $productos.' producto(s) · Total '.number_format((float) $this->pedido->total, 2),
            'pedido_id' => $this->pedido->id,
            'mesa_id' => $this->pedido->mesa_id,
            'mesa' => $etiquetaMesa,
            'url' => route('pedidos.show', $this->pedido),
        ];
    }
}
