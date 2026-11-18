<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PagoMesaService
{
    /**
     * Detalle de pago de una mesa: pedidos activos, líneas y totales.
     *
     * @return array{pedidos: Collection, lineas: array<int, array>, total: float, pagado: float, pendiente: float, completa: bool}
     */
    public function detalleMesa(Mesa $mesa): array
    {
        $pedidos = $mesa->pedidos()
            ->with('productos')
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->orderBy('id')
            ->get();

        return array_merge(['pedidos' => $pedidos], $this->lineasDePedidos($pedidos));
    }

    /**
     * Líneas de pago (pedido_producto) de una colección de pedidos, con estado pagado y totales.
     *
     * @param  Collection<int, Pedido>  $pedidos
     * @return array{lineas: array<int, array>, total: float, pagado: float, pendiente: float, completa: bool}
     */
    public function lineasDePedidos($pedidos): array
    {
        $lineIds = $pedidos->flatMap(fn ($p) => $p->productos->pluck('pivot.id'))->values();
        $pagadas = $lineIds->isEmpty()
            ? collect()
            : DB::table('pago_producto')->whereIn('pedido_producto_id', $lineIds)->pluck('pedido_producto_id');

        $lineas = [];
        foreach ($pedidos as $pedido) {
            foreach ($pedido->productos as $producto) {
                $lineas[] = [
                    'id' => (int) $producto->pivot->id,
                    'pedido_id' => $pedido->id,
                    'producto' => $producto->nombre,
                    'cantidad' => (int) $producto->pivot->cantidad,
                    'precio' => (float) $producto->pivot->precio_unitario,
                    'subtotal' => round($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2),
                    'pagado' => $pagadas->contains((int) $producto->pivot->id),
                ];
            }
        }

        $total = round(array_sum(array_column($lineas, 'subtotal')), 2);
        $pagado = round(array_sum(array_column(array_filter($lineas, fn ($l) => $l['pagado']), 'subtotal')), 2);
        $pendiente = round($total - $pagado, 2);

        return [
            'lineas' => $lineas,
            'total' => $total,
            'pagado' => $pagado,
            'pendiente' => $pendiente,
            'completa' => $pendiente <= 0,
        ];
    }

    /**
     * Registra uno o más pagos sobre las líneas pendientes de la mesa.
     *
     * @param  array{metodo_pago: string, modo: string, lineas?: array<int, int>, partes?: int}  $data
     * @return array{pagos: array<int, Pago>, monto: float}
     *
     * @throws \InvalidArgumentException
     */
    public function pagar(Mesa $mesa, array $data, int $userId): array
    {
        $detalle = $this->detalleMesa($mesa);
        $pendientes = collect($detalle['lineas'])->where('pagado', false)->keyBy('id');

        if ($pendientes->isEmpty()) {
            throw new \InvalidArgumentException('La mesa no tiene productos pendientes de pago.');
        }

        $modo = $data['modo'];
        $pagos = [];

        if ($modo === 'partes') {
            $partes = max(2, (int) ($data['partes'] ?? 0));
            $montos = $this->montosPorParte($detalle['pendiente'], $partes);
            $capacidades = array_map(fn ($m) => (int) round($m * 100), $montos);
            $asignacion = array_fill(0, $partes, []);

            foreach ($pendientes->values()->all() as $linea) {
                $sub = (int) round($linea['subtotal'] * 100);

                $idx = null;
                $mejor = null;
                $mayor = 0;
                foreach ($capacidades as $i => $disponible) {
                    if ($disponible >= $sub && ($mejor === null || $disponible < $mejor)) {
                        $mejor = $disponible;
                        $idx = $i;
                    }
                    if ($disponible > $capacidades[$mayor]) {
                        $mayor = $i;
                    }
                }
                if ($idx === null) {
                    $idx = $mayor;
                }

                $asignacion[$idx][] = $linea;
                $capacidades[$idx] -= $sub;
            }

            foreach ($asignacion as $i => $lineasPersona) {
                $pagos[] = $this->crearPago($mesa, $userId, $data['metodo_pago'], 'partes', $lineasPersona, $partes, $montos[$i]);
            }
        } else {
            if ($modo === 'productos') {
                $elegidas = array_map('intval', $data['lineas'] ?? []);
                $lineas = $pendientes->only($elegidas)->values()->all();
                if (empty($lineas)) {
                    throw new \InvalidArgumentException('Selecciona al menos un producto sin pagar.');
                }
            } else {
                $lineas = $pendientes->values()->all();
            }

            $pagos[] = $this->crearPago($mesa, $userId, $data['metodo_pago'], $modo, $lineas, null);
        }

        return [
            'pagos' => $pagos,
            'monto' => round(array_sum(array_column($pagos, 'monto')), 2),
        ];
    }

    /**
     * Divide el pendiente en N partes iguales exactas (aritmética en centavos).
     * Si no cabe exacto, la última persona absorbe el centavo sobrante.
     *
     * @return array<int, float>
     */
    public function montosPorParte(float $pendiente, int $partes): array
    {
        $partes = max(1, $partes);
        $cents = (int) round($pendiente * 100);
        $base = intdiv($cents, $partes);
        $residuo = $cents - $base * $partes;

        $montos = array_fill(0, $partes, $base);
        $montos[$partes - 1] = $base + $residuo;

        return array_map(fn ($c) => round($c / 100, 2), $montos);
    }

    /**
     * Cierra la mesa: pasa todos sus pedidos activos a "pagado".
     */
    public function cerrarMesa(Mesa $mesa): bool
    {
        $detalle = $this->detalleMesa($mesa);
        if (! $detalle['completa']) {
            return false;
        }

        $ids = $mesa->pedidos()
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return true;
        }

        $mesa->pedidos()
            ->whereIn('id', $ids)
            ->update(['estado' => 'pagado']);

        // El update masivo no dispara eventos del modelo: avisamos aquí.
        $notificador = app(PedidoNotificacionService::class);
        $actorId = auth()->id();

        foreach (Pedido::whereIn('id', $ids)->get() as $pedido) {
            $notificador->actualizado($pedido, $actorId);
        }

        return true;
    }

    /**
     * @param  array<int, array>  $lineas
     */
    private function crearPago(Mesa $mesa, int $userId, string $metodo, string $modo, array $lineas, ?int $partes, ?float $monto = null): Pago
    {
        $monto = round($monto ?? array_sum(array_column($lineas, 'subtotal')), 2);

        return DB::transaction(function () use ($mesa, $userId, $metodo, $modo, $lineas, $partes, $monto) {
            $pago = Pago::create([
                'mesa_id' => $mesa->id,
                'user_id' => $userId,
                'metodo_pago' => $metodo,
                'modo' => $modo,
                'partes' => $partes,
                'monto' => $monto,
            ]);

            if ($lineas !== []) {
                $now = now();
                DB::table('pago_producto')->insert(array_map(fn ($linea) => [
                    'pago_id' => $pago->id,
                    'pedido_producto_id' => $linea['id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $lineas));
            }

            return $pago;
        });
    }
}
