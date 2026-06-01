<?php

namespace App\Http\Controllers;

use App\Jobs\SendPrintOrderJob;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Display a listing of the pedidos.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isStaff()) {
            $pedidos = Pedido::with(['mesa', 'staff', 'productos'])->get();
        } else {
            $pedidos = Pedido::where('user_id', $user->id)->with(['mesa', 'staff', 'productos'])->get();
        }

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new pedido.
     */
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

        $pisos = Mesa::distinct()->whereNotNull('piso')->pluck('piso')->toArray();

        $tables = $mesas->map(function ($mesa) {
            return ['id' => $mesa->id, 'label' => 'Mesa '.$mesa->numero, 'piso' => $mesa->piso];
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
                'capacidad' => $m->capacidad,
                'pos_x' => (int) $m->pos_x,
                'pos_y' => (int) $m->pos_y,
                'forma' => $m->forma,
                'ancho' => (int) $m->ancho,
                'alto' => (int) $m->alto,
                'estado' => $estado,
                'piso' => $m->piso,
            ];
        });

        return view('pedidos.create', compact('mesas', 'categorias', 'productos', 'menu', 'tables', 'pisos', 'planoMesas'));
    }

    /**
     * Store a newly created pedido in storage.
     */
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

        $pedido = Pedido::create([
            'mesa_id' => $request->mesa_id,
            'user_id' => $request->user()->id,
            'estado' => 'pendiente',
            'total' => 0,
        ]);

        $total = 0;
        foreach ($request->productos as $productoData) {
            $producto = Producto::find($productoData['id']);
            $cantidad = $productoData['cantidad'];
            $precioUnitario = $producto->precio;
            $total += $precioUnitario * $cantidad;

            $pedido->productos()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
            ]);
        }

        $pedido->update(['total' => $total]);

        SendPrintOrderJob::dispatch($pedido)->afterResponse();

        if ($request->wantsJson()) {
            return response()->json([
                'pedido' => $pedido->fresh()->load(['mesa', 'productos']),
            ], 201);
        }

        return redirect()->route('pedidos.show', $pedido)
            ->with('success', 'Pedido creado exitosamente.');
    }

    /**
     * Display the specified pedido.
     */
    public function show(Pedido $pedido)
    {
        $user = auth()->user();
        if ($user->isStaff() && ! $user->isAdmin() && ! $user->isSuperAdmin() && $pedido->user_id !== $user->id) {
            abort(403);
        }

        $pedido->load(['mesa', 'staff', 'productos']);

        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Update the estado of the pedido.
     */
    public function updateEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,servido,pagado',
        ]);

        $pedido->update(['estado' => $request->estado]);

        return redirect()->back()->with('success', 'Estado del pedido actualizado.');
    }

    /**
     * Print a factura/total ticket for the pedido.
     */
    public function printFactura(Pedido $pedido)
    {
        $user = auth()->user();
        if ($user->isStaff() && ! $user->isAdmin() && ! $user->isSuperAdmin() && $pedido->user_id !== $user->id) {
            abort(403);
        }

        app(PrintService::class)->printFactura($pedido);

        return redirect()->back()->with('success', 'Factura enviada a impresora.');
    }
}
