<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Zona;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function index()
    {
        $tab = request('tab', 'plano');
        $zonas = Zona::withCount('mesas')->orderBy('orden')->orderBy('nombre')->get();
        $zonaSolicitada = request('zona') ? (int) request('zona') : null;

        if ($zonaSolicitada !== null) {
            $zonaActual = $zonaSolicitada;
        } elseif ($tab === 'plano' && $zonas->isNotEmpty()) {
            $zonaActual = $zonas->first()->id;
        } else {
            $zonaActual = null;
        }

        $query = Mesa::orderBy('numero');
        if ($zonaActual) {
            $query->where('zona_id', $zonaActual);
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
            'zona_id' => $m->zona_id,
            'disponible' => (bool) $m->disponible,
            'pos_x' => (int) $m->pos_x,
            'pos_y' => (int) $m->pos_y,
            'forma' => $m->forma,
            'ancho' => (int) $m->ancho,
            'alto' => (int) $m->alto,
            'color' => $m->color,
        ])->values();

        return view('mesas.index', compact('mesas', 'zonas', 'zonaActual', 'mesasConPedido', 'mesasConReserva', 'mesasData', 'tab'));
    }

    public function create()
    {
        $zonas = Zona::orderBy('orden')->orderBy('nombre')->get();

        return view('mesas.create', compact('zonas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:mesas',
            'zona_id' => 'nullable|exists:zonas,id',
            'disponible' => 'boolean',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'forma' => 'nullable|string|in:circle,square',
            'ancho' => 'nullable|integer|min:40|max:200',
            'alto' => 'nullable|integer|min:40|max:200',
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $data = $request->only(['numero', 'zona_id', 'disponible', 'pos_x', 'pos_y', 'forma', 'ancho', 'alto', 'color']);
        $data['color'] = $data['color'] ?: null;

        Mesa::create($data);

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa creada exitosamente.');
    }

    public function edit(Mesa $mesa)
    {
        $zonas = Zona::orderBy('orden')->orderBy('nombre')->get();

        return view('mesas.edit', compact('mesa', 'zonas'));
    }

    public function update(Request $request, Mesa $mesa)
    {
        $request->validate([
            'numero' => 'required|string|max:10|unique:mesas,numero,'.$mesa->id,
            'zona_id' => 'nullable|exists:zonas,id',
            'disponible' => 'boolean',
            'pos_x' => 'nullable|integer|min:0',
            'pos_y' => 'nullable|integer|min:0',
            'forma' => 'nullable|string|in:circle,square',
            'ancho' => 'nullable|integer|min:40|max:200',
            'alto' => 'nullable|integer|min:40|max:200',
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $data = $request->only(['numero', 'zona_id', 'disponible', 'pos_x', 'pos_y', 'forma', 'ancho', 'alto', 'color']);
        $data['color'] = $data['color'] ?: null;

        $mesa->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'mesa' => $mesa]);
        }

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa actualizada exitosamente.');
    }

    public function destroy(Request $request, Mesa $mesa)
    {
        $tieneHistorial = $mesa->pedidos()->exists()
            || $mesa->reservas()->exists()
            || $mesa->pagos()->exists();

        if ($tieneHistorial) {
            $message = 'No se puede eliminar la mesa porque tiene pedidos, reservas o pagos asociados.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 409);
            }

            return redirect()->back()->with('error', $message);
        }

        $mesa->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Mesa eliminada exitosamente.']);
        }

        return redirect()->route('admin.mesas.index')
            ->with('success', 'Mesa eliminada exitosamente.');
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
            'mesas.*.color' => 'nullable|string|max:7',
        ]);

        foreach ($request->mesas as $data) {
            Mesa::where('id', $data['id'])->update([
                'pos_x' => $data['pos_x'],
                'pos_y' => $data['pos_y'],
                'forma' => $data['forma'] === 'rectangle' ? 'square' : $data['forma'],
                'ancho' => $data['ancho'],
                'alto' => $data['alto'],
                'color' => $data['color'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Plano guardado exitosamente.']);
    }
}
