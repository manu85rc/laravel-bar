<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with('children')->whereNull('parent_id')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        $categorias = Categoria::whereNull('parent_id')->get();

        return view('admin.categorias.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'parent_id', 'description', 'orden']);
        if (! isset($data['orden']) || $data['orden'] === '') {
            $maxOrden = Categoria::whereNull('parent_id')->max('orden');
            $data['orden'] = ($maxOrden ?? -1) + 1;
        }

        Categoria::create($data);

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Categoria $categoria)
    {
        return view('admin.categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        $categorias = Categoria::whereNull('parent_id')->where('id', '!=', $categoria->id)->get();

        return view('admin.categorias.edit', compact('categoria', 'categorias'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|different:'.$categoria->id,
            'description' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);

        $categoria->update($request->only(['name', 'parent_id', 'description', 'orden']));

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:categories,id',
        ]);

        foreach ($request->orden as $index => $id) {
            Categoria::where('id', $id)->update(['orden' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
