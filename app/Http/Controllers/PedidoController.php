<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the pedidos.
     */
    public function index()
    {
        $pedidos = Pedido::with(['mesa', 'camarero', 'productos'])->get();
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new pedido.
     */
    public function create()
    {
        $mesas = Mesa::where('disponible', true)->get();
        $productos = Producto::where('disponible', true)->get();
        return view('pedidos.create', compact('mesas', 'productos'));
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

        $pedido = Pedido::create([
            'mesa_id' => $request->mesa_id,
            'user_id' => $request->user()->id, // assuming the logged-in user is the camarero
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

        return redirect()->route('pedidos.show', $pedido)
                        ->with('success', 'Pedido creado exitosamente.');
    }

    /**
     * Display the specified pedido.
     */
    public function show(Pedido $pedido)
    {
        $pedido->load(['mesa', 'camarero', 'productos']);
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
}