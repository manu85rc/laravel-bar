<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MercadoPagoSetting;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Zona;
use App\Services\MercadoPagoService;
use App\Services\PagoMesaService;
use App\Services\PedidoService;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        return view('pedidos.index', $this->datosListado($request));
    }

    /**
     * Fragmento HTML + datos del listado para refrescar /pedidos en tiempo
     * real (vía WebSocket) sin recargar la página completa.
     */
    public function fragmento(Request $request)
    {
        $datos = $this->datosListado($request);

        return response()->json([
            'html' => view('pedidos._contenido', $datos)->render(),
            'grupos' => $datos['gruposPago'],
            'activosCount' => $datos['activosCount'],
            'historialCount' => $datos['historialCount'],
        ]);
    }

    /**
     * Arma los datos del listado (tab activo/historial y sus grupos) usado
     * tanto por la vista completa como por el fragmento en tiempo real.
     *
     * @return array<string, mixed>
     */
    private function datosListado(Request $request): array
    {
        $user = auth()->user();

        $puedeVerMp = $user->isSuperAdmin() || $user->hasPermission('admin.mercadopago');

        $tab = $request->get('tab', 'activos');
        if ($tab === 'mercadopago' && ! $puedeVerMp) {
            $tab = 'activos';
        }

        $base = Pedido::with(['mesa', 'staff', 'productos']);
        if (! $user->isStaff() && ! $user->hasPermission('admin.pedidos')) {
            $base->where('user_id', $user->id);
        }

        $activosCount = (clone $base)->whereNotIn('estado', ['pagado', 'cancelado'])->count();
        $historialCount = (clone $base)->whereIn('estado', ['pagado', 'cancelado'])->count();

        $pedidos = collect();
        $grupos = collect();
        $gruposPago = [];
        $mpMovimientos = collect();
        $mpSettings = null;

        if ($tab === 'mercadopago') {
            $mpSettings = MercadoPagoSetting::getInstance();
            $mpMovimientos = collect(app(MercadoPagoService::class)->movimientos($mpSettings));
        } elseif ($tab === 'historial') {
            $pedidos = (clone $base)->whereIn('estado', ['pagado', 'cancelado'])->orderByDesc('updated_at')->get();
        } else {
            $pedidos = (clone $base)->whereNotIn('estado', ['pagado', 'cancelado'])->get();

            $lineasSvc = app(PagoMesaService::class);
            $grupos = $pedidos->groupBy('mesa_id')->map(function ($items) use ($lineasSvc) {
                $items = $items->sortBy('id');
                $mesa = $items->first()->mesa;
                $pago = $lineasSvc->lineasDePedidos($items);

                return [
                    'mesa' => $mesa,
                    'pedidos' => $items,
                    'count' => $items->count(),
                    'totalSum' => $pago['total'],
                    'abiertaDesde' => $items->min('created_at'),
                    'lineas' => $pago['lineas'],
                    'pagadoSum' => $pago['pagado'],
                    'pendienteSum' => $pago['pendiente'],
                    'completa' => $pago['completa'],
                ];
            })->sortBy(function ($grupo) {
                return $grupo['mesa'] ? $grupo['mesa']->numero : PHP_INT_MAX;
            })->values();

            $gruposPago = $grupos->map(fn ($g) => [
                'mesa' => $g['mesa'] ? ['id' => $g['mesa']->id, 'numero' => $g['mesa']->numero] : null,
                'total' => (float) $g['totalSum'],
                'pagado' => (float) $g['pagadoSum'],
                'pendiente' => (float) $g['pendienteSum'],
                'lineas' => collect($g['lineas'])->reject(fn ($l) => $l['pagado'])->values()->all(),
            ])->all();
        }

        return compact('pedidos', 'grupos', 'tab', 'activosCount', 'historialCount', 'gruposPago', 'puedeVerMp', 'mpMovimientos', 'mpSettings');
    }

    public function create()
    {
        $mesas = Mesa::where('disponible', true)->get();
        $categorias = Categoria::with(['productos' => function ($q) {
            $q->where('disponible', true);
        }])->get();
        $productos = Producto::where('disponible', true)->get();

        $menu = $categorias->filter(function ($cat) {
            return $cat->productos->isNotEmpty();
        })->mapWithKeys(function ($cat) {
            return [$cat->name => $cat->productos->map(function ($prod) {
                return ['id' => $prod->id, 'name' => $prod->nombre, 'price' => (int) $prod->precio];
            })];
        });

        $zonas = Zona::orderBy('orden')->orderBy('nombre')->pluck('nombre')->toArray();

        $tables = $mesas->map(function ($mesa) {
            return ['id' => $mesa->id, 'label' => 'Mesa '.$mesa->numero, 'piso' => $mesa->zona ? $mesa->zona->nombre : ''];
        });

        $pedidoMesaIds = Pedido::whereNotIn('estado', ['pagado', 'cancelado'])
            ->pluck('mesa_id')->unique()->toArray();
        $reservaMesaIds = Reserva::whereDate('fecha_hora', today())
            ->where('estado', 'confirmada')
            ->pluck('mesa_id')->unique()->toArray();

        $planoMesas = $mesas->map(function ($m) use ($pedidoMesaIds, $reservaMesaIds) {
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
                'color' => $m->color,
                'piso' => $m->zona ? $m->zona->nombre : '',
            ];
        });

        return view('pedidos.create', compact('mesas', 'categorias', 'productos', 'menu', 'tables', 'zonas', 'planoMesas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        Log::info('Creando pedido', [
            'user_id' => $request->user()?->id,
            'mesa_id' => $request->mesa_id,
            'productos' => $request->productos,
        ]);

        $pedido = app(PedidoService::class)->crear($request->only(['mesa_id', 'productos']), $request->user()->id);

        app(PedidoService::class)->dispatchPrint($pedido);

        if ($request->wantsJson()) {
            return response()->json([
                'pedido' => $pedido->fresh()->load(['mesa', 'productos']),
            ], 201);
        }

        return redirect()->route('pedidos.show', $pedido)
            ->with('success', 'Pedido creado exitosamente.');
    }

    public function show(Pedido $pedido)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->hasPermission('admin.pedidos') && $pedido->user_id !== $user->id) {
            abort(403);
        }

        $pedido->load(['mesa', 'staff', 'productos']);

        return view('pedidos.show', compact('pedido'));
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        if ($request->estado === 'pagado') {
            return redirect()->back()->with('error', 'Para marcar como pagado, registra el pago desde la mesa (botón Pagar).');
        }

        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,servido',
        ]);

        $pedido->update(['estado' => $request->estado]);

        return redirect()->back()->with('success', 'Estado del pedido actualizado.');
    }

    public function printFactura(Pedido $pedido)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->hasPermission('admin.pedidos') && $pedido->user_id !== $user->id) {
            abort(403);
        }

        app(PrintService::class)->printFactura($pedido);

        return redirect()->back()->with('success', 'Factura enviada a impresora.');
    }

    public function printFacturaMesa(Mesa $mesa)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->hasPermission('admin.pedidos')) {
            $esDuenio = $mesa->pedidos()
                ->whereNotIn('estado', ['pagado', 'cancelado'])
                ->where('user_id', $user->id)
                ->exists();
            if (! $esDuenio) {
                abort(403);
            }
        }

        $ok = app(PrintService::class)->printFacturaMesa($mesa);

        if (! $ok) {
            return redirect()->back()->with('error', 'No hay pedidos activos o no hay impresora de factura configurada.');
        }

        return redirect()->back()->with('success', 'Factura de la mesa enviada a impresora.');
    }

    public function pagarMesa(Request $request, Mesa $mesa)
    {
        $data = $request->validate([
            'metodo_pago' => 'required|in:efectivo,tarjeta,qr,otro',
            'modo' => 'required|in:total,productos,partes',
            'lineas' => 'required_if:modo,productos|array',
            'lineas.*' => 'integer|exists:pedido_producto,id',
            'partes' => 'nullable|required_if:modo,partes|integer|min:2|max:30',
        ]);

        try {
            $resultado = app(PagoMesaService::class)->pagar($mesa, $data, $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pago de $'.number_format($resultado['monto'], 0).' registrado.');
    }

    public function cerrarMesa(Mesa $mesa)
    {
        $ok = app(PagoMesaService::class)->cerrarMesa($mesa);

        if (! $ok) {
            return redirect()->back()->with('error', 'La mesa aún tiene productos sin pagar.');
        }

        return redirect()->back()->with('success', 'Mesa cerrada. Los pedidos pasaron a pagados.');
    }
}
