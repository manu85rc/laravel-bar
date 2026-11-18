<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento en tiempo real que avisa al personal conectado (canal `staff`)
 * de que un pedido se creó o cambió de estado.
 *
 * Implementa ShouldBroadcastNow para difundirse al instante sin necesidad
 * de un worker de colas.
 */
class PedidoCambio implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Pedido $pedido,
        public string $accion = 'actualizado',
        public ?int $actorId = null,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('staff')];
    }

    public function broadcastAs(): string
    {
        return 'pedido.cambio';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $mesa = $this->pedido->relationLoaded('mesa')
            ? $this->pedido->mesa
            : $this->pedido->mesa()->first();

        return [
            'pedido_id' => $this->pedido->id,
            'mesa_id' => $this->pedido->mesa_id,
            'mesa' => $mesa ? 'Mesa '.$mesa->numero : null,
            'estado' => $this->pedido->estado,
            'accion' => $this->accion,
            'actor_id' => $this->actorId,
        ];
    }
}
