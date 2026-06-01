<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Reserva;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Mesa::orderBy('numero');
        $pisoActual = request('piso');
        $pisos = Mesa::distinct()->whereNotNull('piso')->pluck('piso')->toArray();
        if ($pisoActual) {
            $query->where('piso', $pisoActual);
        }
        $mesas = $query->get();

        return view('mesas.index', compact('mesas', 'pisos', 'pisoActual'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('mesas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:mesas',
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50',
            'piso' => 'nullable|string|max:50',
            'disponible' => 'boolean',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'forma' => 'nullable|string|in:circle,square',
            'ancho' => 'nullable|integer|min:40|max:200',
            'alto' => 'nullable|integer|min:40|max:200',
        ]);

        Mesa::create($request->only(['numero', 'capacidad', 'ubicacion', 'piso', 'disponible', 'pos_x', 'pos_y', 'forma', 'ancho', 'alto']));

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mesa $mesa)
    {
        return view('mesas.edit', compact('mesa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mesa $mesa)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:mesas,numero,'.$mesa->id,
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50',
            'piso' => 'nullable|string|max:50',
            'disponible' => 'boolean',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'forma' => 'nullable|string|in:circle,square',
            'ancho' => 'nullable|integer|min:40|max:200',
            'alto' => 'nullable|integer|min:40|max:200',
        ]);

        $mesa->update($request->only(['numero', 'capacidad', 'ubicacion', 'piso', 'disponible', 'pos_x', 'pos_y', 'forma', 'ancho', 'alto']));

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mesa $mesa)
    {
        $mesa->delete();

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa eliminada exitosamente.');
    }

    public function plano()
    {
        $pisos = Mesa::distinct()->whereNotNull('piso')->pluck('piso')->toArray();
        $pisoActual = request('piso', $pisos[0] ?? null);

        $query = Mesa::orderBy('numero');
        if ($pisoActual) {
            $query->where('piso', $pisoActual);
        }
        $mesas = $query->get();

        $mesasConPedido = Pedido::whereNotIn('estado', ['pagado', 'cancelado'])
            ->pluck('mesa_id')
            ->unique()
            ->toArray();
        $mesasConReserva = Reserva::whereDate('fecha_hora', today())
            ->where('estado', 'confirmada')
            ->pluck('mesa_id')
            ->unique()
            ->toArray();

        $mesasData = $mesas->map(fn ($m) => [
            'id' => $m->id,
            'numero' => $m->numero,
            'capacidad' => $m->capacidad,
            'pos_x' => (int) $m->pos_x,
            'pos_y' => (int) $m->pos_y,
            'forma' => $m->forma,
            'ancho' => (int) $m->ancho,
            'alto' => (int) $m->alto,
        ])->values();

        return view('mesas.plano', compact('mesas', 'mesasConPedido', 'mesasConReserva', 'mesasData', 'pisos', 'pisoActual'));
    }

    public function guardarPlano(Request $request)
    {
        $request->validate([
            'mesas' => 'required|array',
            'mesas.*.id' => 'required|exists:mesas,id',
            'mesas.*.pos_x' => 'required|integer|min:0',
            'mesas.*.pos_y' => 'required|integer|min:0',
            'mesas.*.forma' => 'required|string|in:circle,square,rectangle',
            'mesas.*.ancho' => 'required|integer|min:40|max:200',
            'mesas.*.alto' => 'required|integer|min:40|max:200',
        ]);

        foreach ($request->mesas as $data) {
            Mesa::where('id', $data['id'])->update([
                'pos_x' => $data['pos_x'],
                'pos_y' => $data['pos_y'],
                'forma' => $data['forma'] === 'rectangle' ? 'square' : $data['forma'],
                'ancho' => $data['ancho'],
                'alto' => $data['alto'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Plano guardado exitosamente.']);
    }
}
