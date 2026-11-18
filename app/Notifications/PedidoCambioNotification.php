<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificación interna (canal database) que avisa a TODO el personal
 * (admin, super_admin y staff) cuando se crea un pedido o cambia de estado.
 *
 * El payload se guarda en español para que la campana lo muestre tal cual.
 */
class PedidoCambioNotification extends Notification
{
    use Queueable;

    public const ACCION_CREADO = 'creado';

    public const ACCION_ACTUALIZADO = 'actualizado';

    public function __construct(
        public Pedido $pedido,
        public string $accion = self::ACCION_ACTUALIZADO,
        public ?string $actorNombre = null,
    ) {}

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
        $estado = $this->etiquetaEstado((string) $this->pedido->estado);

        $esCreacion = $this->accion === self::ACCION_CREADO;

        $mensaje = $esCreacion
            ? $etiquetaMesa.': se creó el pedido #'.$this->pedido->id.'.'
            : $etiquetaMesa.': el pedido #'.$this->pedido->id.' pasó a '.$estado.'.';

        if ($this->actorNombre) {
            $mensaje .= ' ('.$this->actorNombre.')';
        }

        return [
            'tipo' => $esCreacion ? 'pedido_creado' : 'pedido_actualizado',
            'icono' => $esCreacion ? 'fa-solid fa-receipt' : 'fa-solid fa-arrows-rotate',
            'color' => $this->pedido->estado === 'listo' ? 'verde' : 'oro',
            'titulo' => $esCreacion ? 'Nuevo pedido' : 'Pedido actualizado',
            'mensaje' => $mensaje,
            'detalle' => $productos.' producto(s) · Total '.number_format((float) $this->pedido->total, 2),
            'pedido_id' => $this->pedido->id,
            'mesa_id' => $this->pedido->mesa_id,
            'mesa' => $etiquetaMesa,
            'estado' => $this->pedido->estado,
            'accion' => $this->accion,
            'url' => route('pedidos.show', $this->pedido),
        ];
    }

    private function etiquetaEstado(string $estado): string
    {
        return [
            'pendiente' => 'Pendiente',
            'preparando' => 'Preparando',
            'listo' => 'Listo',
            'servido' => 'Servido',
            'pagado' => 'Pagado',
            'cancelado' => 'Cancelado',
        ][$estado] ?? ucfirst($estado);
    }
}
