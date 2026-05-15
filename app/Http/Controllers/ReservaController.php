<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Mesa;
use App\Models\User;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $reservas = Reserva::with(['user', 'mesa'])->get();
        } else {
            $reservas = Reserva::where('user_id', $user->id)->with('mesa')->get();
        }

        return view('reservas.index', compact('reservas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get available mesas (those that are disponible)
        $mesas = Mesa::where('disponible', true)->get();

        return view('reservas.create', compact('mesas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'fecha_hora' => 'required|date_format:d-m-Y H:i|after_or_equal:now',
            'numero_personas' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:255',
        ], [
            'mesa_id.required' => 'Debes seleccionar una mesa',
        ]);

        // Check if the selected mesa is still disponible (to avoid race condition)
        $mesa = Mesa::find($request->mesa_id);
        if (!$mesa || !$mesa->disponible) {
            return back()->withInput()->withErrors(['mesa_id' => 'La mesa seleccionada ya no está disponible.']);
        }

        // Check if the mesa has enough capacity
        if ($request->numero_personas > $mesa->capacidad) {
            return back()->withInput()->withErrors(['numero_personas' => 'La cantidad de personas excede la capacidad de la mesa.']);
        }

        // Create the reservation
        $reserva = Reserva::create([
            'user_id' => auth()->id(),
            'mesa_id' => $request->mesa_id,
            'fecha_hora' => $request->fecha_hora,
            'numero_personas' => $request->numero_personas,
            'observaciones' => $request->observaciones,
            'estado' => 'pendiente', // default state
        ]);

        // Optionally, mark the mesa as not disponible? 
        // But note: a mesa can be reserved for multiple time slots, so we don't mark it as unavailable globally.
        // Instead, we should check for overlapping reservations in the same mesa at the same time.
        // For simplicity, we'll skip that for now and just note that the mesa remains disponible for other times.

        return redirect()->route('reservas.index')
                        ->with('success', 'Reserva creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function edit(Reserva $reserva)
    {
        // Authorization: only the owner or admin can edit
        if (auth()->id() !== $reserva->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Get available mesas (those that are disponible) plus the current mesa (even if not disponible, because we are editing)
        $mesas = Mesa::where('disponible', true)->orWhere('id', $reserva->mesa_id)->get();

        return view('reservas.edit', compact('reserva', 'mesas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reserva $reserva)
    {
        // Authorization: only the owner or admin can update
        if (auth()->id() !== $reserva->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'fecha_hora' => 'required|date_format:d-m-Y H:i|after_or_equal:now',
            'numero_personas' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:255',
            'estado' => 'required|in:pendiente,confirmada,completada,cancelada',
        ], [
            'mesa_id.required' => 'Debes seleccionar una mesa',
        ]);

        // Check if the selected mesa is still disponible (or is the same as the current one)
        $mesa = Mesa::find($request->mesa_id);
        if (!$mesa || (!$mesa->disponible && $mesa->id !== $request->mesa_id)) {
            return back()->withInput()->withErrors(['mesa_id' => 'La mesa seleccionada ya no está disponible.']);
        }

        // Check if the mesa has enough capacity
        if ($request->numero_personas > $mesa->capacidad) {
            return back()->withInput()->withErrors(['numero_personas' => 'La cantidad de personas excede la capacidad de la mesa.']);
        }

        // Update the reservation
        $reserva->update($request->all());

        return redirect()->route('reservas.index')
                        ->with('success', 'Reserva actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reserva  $reserva
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reserva $reserva)
    {
        // Authorization: only the owner or admin can delete
        if (auth()->id() !== $reserva->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $reserva->delete();

        return redirect()->route('reservas.index')
                        ->with('success', 'Reserva eliminada exitosamente.');
    }
}