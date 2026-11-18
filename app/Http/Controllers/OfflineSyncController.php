<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\OfflineSync;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Services\PagoMesaService;
use App\Services\PedidoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OfflineSyncController extends Controller
{
    /**
     * Aplica un lote de operaciones pendientes (pedidos creados offline).
     * Idempotente: si ya existe un registro con el mismo client_id, se
     * devuelve el resultado previo en lugar de duplicar el pedido.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'ops' => 'required|array',
            'ops.*.client_id' => 'required|string',
            'ops.*.type' => 'required|string',
        ]);

        $results = [];

        foreach ($request->ops as $op) {
            $results[] = $this->applyOp($op);
        }

        return response()->json(['results' => $results]);
    }

    protected function applyOp(array $op)
    {
        $clientId = $op['client_id'];
        $type = $op['type'] ?? 'pedido';
        $userId = auth()->id();

        $existing = OfflineSync::where('client_id', $clientId)->first();
        if ($existing && $existing->estado === 'sincronizado') {
            return [
                'client_id' => $clientId,
                'ok' => true,
                'server_id' => $existing->server_id,
                'duplicated' => true,
            ];
        }

        switch ($type) {
            case 'pedido':
                return $this->applyPedido($op, $clientId, $userId);
            case 'pedido_estado':
                return $this->applyPedidoEstado($op, $clientId, $userId);
            case 'pago_mesa':
                return $this->applyPagoMesa($op, $clientId, $userId);
            case 'cerrar_mesa':
                return $this->applyCerrarMesa($op, $clientId, $userId);
            default:
                return [
                    'client_id' => $clientId,
                    'ok' => false,
                    'fatal' => true,
                    'error' => 'Tipo de operación no soportado: '.$type,
                ];
        }
    }

    protected function applyPedido(array $op, string $clientId, ?int $userId): array
    {
        try {
            $pedido = DB::transaction(function () use ($clientId, $userId, $op) {
                $pedido = $this->createPedidoOffline($op)['pedido'];
                $this->marcarSincronizado($clientId, 'pedido', $userId, $pedido->id, $op);

                return $pedido;
            });

            return [
                'client_id' => $clientId,
                'ok' => true,
                'server_id' => $pedido->id,
                'pedido' => [
                    'id' => $pedido->id,
                    'mesa_id' => $pedido->mesa_id,
                    'estado' => $pedido->estado,
                    'total' => $pedido->total,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Sync offline pedido falló', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->marcarError($clientId, 'pedido', $userId, $op, $e->getMessage());

            return [
                'client_id' => $clientId,
                'ok' => false,
                'error' => 'No se pudo sincronizar el pedido.',
            ];
        }
    }

    protected function applyPedidoEstado(array $op, string $clientId, ?int $userId): array
    {
        $validador = Validator::make($op, [
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'estado' => 'required|in:pendiente,preparando,listo,servido',
        ]);
        if ($validador->fails()) {
            return $this->falloFatal($clientId, 'pedido_estado', $userId, $op, 'Estado de pedido inválido.');
        }

        $orden = [
            'pendiente' => 0,
            'preparando' => 1,
            'listo' => 2,
            'servido' => 3,
            'pagado' => 4,
        ];

        $pedido = Pedido::find($op['pedido_id']);
        $actual = $pedido->estado;
        $destino = $op['estado'];
        if (! array_key_exists($actual, $orden) || ! array_key_exists($destino, $orden)) {
            return $this->falloFatal($clientId, 'pedido_estado', $userId, $op, 'Estado de pedido desconocido.');
        }

        if ($orden[$destino] <= $orden[$actual]) {
            $this->marcarSincronizado($clientId, 'pedido_estado', $userId, $pedido->id, $op);

            return [
                'client_id' => $clientId,
                'ok' => true,
                'skipped' => true,
                'motivo' => 'estado_no_adelanta',
                'estado' => $actual,
                'server_id' => $pedido->id,
            ];
        }

        try {
            DB::transaction(function () use ($pedido, $destino, $clientId, $userId, $op) {
                $pedido->update(['estado' => $destino]);
                $this->marcarSincronizado($clientId, 'pedido_estado', $userId, $pedido->id, $op);
            });

            return [
                'client_id' => $clientId,
                'ok' => true,
                'estado' => $destino,
                'server_id' => $pedido->id,
            ];
        } catch (\Throwable $e) {
            Log::error('Sync offline cambio de estado falló', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->marcarError($clientId, 'pedido_estado', $userId, $op, $e->getMessage());

            return [
                'client_id' => $clientId,
                'ok' => false,
                'error' => 'No se pudo actualizar el estado.',
            ];
        }
    }

    protected function applyPagoMesa(array $op, string $clientId, ?int $userId): array
    {
        $validador = Validator::make($op, [
            'mesa_id' => 'required|integer|exists:mesas,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta,qr,otro',
            'modo' => 'required|in:total,productos',
            'lineas' => 'required_if:modo,productos|array',
            'lineas.*' => 'integer|exists:pedido_producto,id',
        ]);
        if ($validador->fails()) {
            return $this->falloFatal($clientId, 'pago_mesa', $userId, $op, 'Pago offline inválido. El modo partes requiere conexión.');
        }

        $mesa = Mesa::find($op['mesa_id']);

        try {
            $resultado = DB::transaction(function () use ($op, $clientId, $userId, $mesa) {
                $resultado = app(PagoMesaService::class)->pagar($mesa, [
                    'metodo_pago' => $op['metodo_pago'],
                    'modo' => $op['modo'],
                    'lineas' => $op['lineas'] ?? [],
                ], $userId);
                $serverId = $resultado['pagos'][0]->id ?? $mesa->id;
                $this->marcarSincronizado($clientId, 'pago_mesa', $userId, (int) $serverId, $op);

                return $resultado;
            });

            return [
                'client_id' => $clientId,
                'ok' => true,
                'server_id' => (int) ($resultado['pagos'][0]->id ?? $mesa->id),
                'monto' => $resultado['monto'],
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->falloFatal($clientId, 'pago_mesa', $userId, $op, $e->getMessage());
        } catch (QueryException $e) {
            Log::warning('Sync offline pago duplicado', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->marcarSincronizado($clientId, 'pago_mesa', $userId, $mesa->id, $op);

            return [
                'client_id' => $clientId,
                'ok' => true,
                'skipped' => true,
                'motivo' => 'pago_ya_registrado',
                'server_id' => $mesa->id,
            ];
        } catch (\Throwable $e) {
            Log::error('Sync offline pago falló', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->marcarError($clientId, 'pago_mesa', $userId, $op, $e->getMessage());

            return [
                'client_id' => $clientId,
                'ok' => false,
                'error' => 'No se pudo registrar el pago.',
            ];
        }
    }

    protected function applyCerrarMesa(array $op, string $clientId, ?int $userId): array
    {
        $validador = Validator::make($op, [
            'mesa_id' => 'required|integer|exists:mesas,id',
        ]);
        if ($validador->fails()) {
            return $this->falloFatal($clientId, 'cerrar_mesa', $userId, $op, 'Mesa inválida.');
        }

        $mesa = Mesa::find($op['mesa_id']);

        try {
            $cerrada = DB::transaction(function () use ($clientId, $userId, $op, $mesa) {
                if (! app(PagoMesaService::class)->cerrarMesa($mesa)) {
                    return false;
                }
                $this->marcarSincronizado($clientId, 'cerrar_mesa', $userId, $mesa->id, $op);

                return true;
            });

            if (! $cerrada) {
                return [
                    'client_id' => $clientId,
                    'ok' => false,
                    'error' => 'La mesa aún tiene productos sin pagar.',
                ];
            }

            return [
                'client_id' => $clientId,
                'ok' => true,
                'server_id' => $mesa->id,
            ];
        } catch (\Throwable $e) {
            Log::error('Sync offline cierre de mesa falló', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->marcarError($clientId, 'cerrar_mesa', $userId, $op, $e->getMessage());

            return [
                'client_id' => $clientId,
                'ok' => false,
                'error' => 'No se pudo cerrar la mesa.',
            ];
        }
    }

    protected function falloFatal(string $clientId, string $type, ?int $userId, array $op, string $mensaje): array
    {
        $this->marcarError($clientId, $type, $userId, $op, $mensaje);

        return [
            'client_id' => $clientId,
            'ok' => false,
            'fatal' => true,
            'error' => $mensaje,
        ];
    }

    protected function marcarSincronizado(string $clientId, string $type, ?int $userId, ?int $serverId, array $op): void
    {
        OfflineSync::updateOrCreate(
            ['client_id' => $clientId],
            [
                'type' => $type,
                'user_id' => $userId,
                'server_id' => $serverId,
                'payload' => $op,
                'estado' => 'sincronizado',
                'error' => null,
            ]
        );
    }

    protected function marcarError(string $clientId, string $type, ?int $userId, array $op, string $error): void
    {
        OfflineSync::updateOrCreate(
            ['client_id' => $clientId],
            [
                'type' => $type,
                'user_id' => $userId,
                'payload' => $op,
                'estado' => 'error',
                'error' => $error,
            ]
        );
    }

    protected function createPedidoOffline(array $op): array
    {
        $mesaId = $op['mesa_id'] ?? $op['payload']['mesa_id'] ?? null;
        $productos = $op['productos'] ?? $op['payload']['productos'] ?? null;
        $userId = auth()->id();

        if (! $mesaId || ! is_array($productos) || ! count($productos)) {
            throw new \InvalidArgumentException('Payload de pedido inválido');
        }

        $productsData = collect($productos)->map(function ($p) {
            return ['id' => $p['id'] ?? null, 'cantidad' => $p['cantidad'] ?? 1];
        })->all();

        $ids = collect($productsData)->pluck('id')->filter()->unique()->all();
        $existentes = Producto::whereIn('id', $ids)->pluck('id')->all();
        if (count($existentes) !== count($ids)) {
            throw new \InvalidArgumentException('Producto inexistente');
        }

        $service = app(PedidoService::class);
        $pedido = $service->crear(['mesa_id' => $mesaId, 'productos' => $productsData], $userId);
        // El print se dispara al sincronizar (cuando hay conexión).
        $service->dispatchPrint($pedido);

        return ['pedido' => $pedido, 'dispatched' => true];
    }

    /**
     * Snapshot del estado de las mesas para precache offline.
     */
    public function mesas()
    {
        $mesas = Mesa::where('disponible', true)->get();

        $pedidoMesaIds = Pedido::whereNotIn('estado', ['pagado', 'cancelado'])
            ->pluck('mesa_id')->unique()->toArray();
        $reservaMesaIds = Reserva::whereDate('fecha_hora', today())
            ->where('estado', 'confirmada')
            ->pluck('mesa_id')->unique()->toArray();

        $data = $mesas->map(function ($m) use ($pedidoMesaIds, $reservaMesaIds) {
            $estado = 'free';
            if (in_array($m->id, $pedidoMesaIds)) {
                $estado = 'occupied';
            } elseif (in_array($m->id, $reservaMesaIds)) {
                $estado = 'reserved';
            }

            return [
                'id' => $m->id,
                'numero' => $m->numero,
                'pos_x' => (int) $m->pos_x,
                'pos_y' => (int) $m->pos_y,
                'forma' => $m->forma,
                'ancho' => (int) $m->ancho,
                'alto' => (int) $m->alto,
                'estado' => $estado,
                'piso' => $m->zona ? $m->zona->nombre : '',
            ];
        });

        return response()->json($data);
    }

    /**
     * Snapshot del menú (categorías con productos disponibles) para precache offline.
     */
    public function menu()
    {
        $categorias = Categoria::with(['productos' => function ($q) {
            $q->where('disponible', true);
        }])->get();

        $menu = $categorias->filter(function ($cat) {
            return $cat->productos->isNotEmpty();
        })->mapWithKeys(function ($cat) {
            return [$cat->name => $cat->productos->map(function ($prod) {
                return [
                    'id' => $prod->id,
                    'name' => $prod->nombre,
                    'price' => (int) $prod->precio,
                ];
            })];
        });

        return response()->json($menu);
    }

    public function pedidos()
    {
        $user = auth()->user();
        $base = Pedido::with(['mesa', 'staff', 'productos']);
        if (! $user->isStaff() && ! $user->hasPermission('admin.pedidos')) {
            $base->where('user_id', $user->id);
        }

        $pedidos = $base->whereNotIn('estado', ['pagado', 'cancelado'])
            ->orderBy('id')
            ->get();
        $lineasSvc = app(PagoMesaService::class);

        $grupos = $pedidos->groupBy('mesa_id')->map(function ($items) use ($lineasSvc) {
            $items = $items->sortBy('id');
            $mesa = $items->first()->mesa;
            $pago = $lineasSvc->lineasDePedidos($items);

            return [
                'mesa' => $mesa ? ['id' => $mesa->id, 'numero' => $mesa->numero] : null,
                'pedidos' => $items->map(function ($pedido) {
                    return [
                        'id' => $pedido->id,
                        'estado' => $pedido->estado,
                        'total' => (float) $pedido->total,
                        'hora' => $pedido->created_at ? $pedido->created_at->format('H:i') : '',
                        'staff' => $pedido->staff ? $pedido->staff->name : null,
                    ];
                })->values()->all(),
                'lineas' => $pago['lineas'],
                'total' => (float) $pago['total'],
                'pagado' => (float) $pago['pagado'],
                'pendiente' => (float) $pago['pendiente'],
                'completa' => $pago['completa'],
            ];
        })->sortBy(function ($grupo) {
            return $grupo['mesa'] ? (int) $grupo['mesa']['numero'] : PHP_INT_MAX;
        })->values()->all();

        return response()->json([
            'grupos' => $grupos,
            'actualizado' => now()->timestamp,
        ]);
    }

    public function session()
    {
        $user = auth()->user();

        return response()->json([
            'is_staff' => $user->isStaff(),
            'nombre' => $user->name,
            'puede_admin_pedidos' => $user->isSuperAdmin() || $user->hasPermission('admin.pedidos'),
        ]);
    }
}
