<?php

namespace App\Services;

use App\Jobs\SendPrintOrderJob;
use App\Models\Pedido;
use App\Models\Producto;

class PedidoService
{
    /**
     * Crea un pedido y sus productos. Reutilizable desde el controlador web
     * y desde el endpoint de sincronización offline.
     */
    public function crear(array $data, ?int $userId): Pedido
    {
        $pedido = Pedido::create([
            'mesa_id' => $data['mesa_id'],
            'user_id' => $userId,
            'estado' => 'pendiente',
            'total' => 0,
        ]);

        $total = 0;
        foreach ($data['productos'] as $productoData) {
            $producto = Producto::find($productoData['id']);
            if (! $producto) {
                continue;
            }
            $cantidad = (int) $productoData['cantidad'];
            $precioUnitario = $producto->precio;
            $total += $precioUnitario * $cantidad;

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
            ]);
        }

        $pedido->update(['total' => $total]);

        // Avisa a todo el personal (excepto a quien lo creó) en tiempo real.
        app(PedidoNotificacionService::class)->creado($pedido, $userId);

        return $pedido;
    }

    public function dispatchPrint(Pedido $pedido): void
    {
        SendPrintOrderJob::dispatch($pedido)->afterResponse();
    }
}
