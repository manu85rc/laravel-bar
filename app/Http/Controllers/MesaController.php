<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mesas = Mesa::all();
        return view('mesas.index', compact('mesas'));
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
            'disponible' => 'boolean',
        ]);

        Mesa::create($request->except('_token'));

        return redirect()->route('mesas.index')
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
            'numero' => 'required|string|max:10|unique:mesas,numero,' . $mesa->id,
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50',
            'disponible' => 'boolean',
        ]);

        $mesa->update($request->except('_token'));

        return redirect()->route('mesas.index')
                        ->with('success', 'Mesa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mesa $mesa)
    {
        $mesa->delete();

        return redirect()->route('mesas.index')
                        ->with('success', 'Mesa eliminada exitosamente.');
    }
}
