<?php

namespace App\Http\Controllers;

use App\Models\Zona;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    public function index()
    {
        $zonas = Zona::withCount('mesas')->orderBy('orden')->orderBy('nombre')->get();

        return response()->json($zonas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:zonas,nombre',
        ]);

        $zona = Zona::create($request->only('nombre'));

        return response()->json(['success' => true, 'zona' => $zona]);
    }

    public function update(Request $request, Zona $zona)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:zonas,nombre,'.$zona->id,
        ]);

        $zona->update($request->only('nombre'));

        return response()->json(['success' => true, 'zona' => $zona]);
    }

    public function destroy(Zona $zona)
    {
        if ($zona->mesas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: hay mesas asignadas a esta zona.',
            ], 422);
        }

        $zona->delete();

        return response()->json(['success' => true, 'message' => 'Zona eliminada exitosamente.']);
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'zonas' => 'required|array',
            'zonas.*.id' => 'required|exists:zonas,id',
            'zonas.*.orden' => 'required|integer|min:1',
            'zonas.*.nombre' => 'nullable|string|max:50',
        ]);

        foreach ($request->zonas as $data) {
            $zona = Zona::find($data['id']);

            if (array_key_exists('nombre', $data) && trim((string) $data['nombre']) === '') {
                $zona->mesas()->update(['zona_id' => null]);
                $zona->delete();

                continue;
            }

            $zona->update(['orden' => $data['orden']]);

            if (array_key_exists('nombre', $data)) {
                $nombre = trim((string) $data['nombre']);

                $duplicado = Zona::where('nombre', $nombre)
                    ->where('id', '!=', $zona->id)
                    ->exists();

                if ($duplicado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una zona con el nombre "'.$nombre.'".',
                    ], 422);
                }

                $zona->update(['nombre' => $nombre]);
            }
        }

        $reload = $request->input('zonas') && collect($request->zonas)->contains(function ($d) {
            return array_key_exists('nombre', $d) && trim((string) $d['nombre']) === '';
        });

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado exitosamente.',
            'reload' => $reload,
        ]);
    }
}
