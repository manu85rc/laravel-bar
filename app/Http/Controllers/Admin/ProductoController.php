<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.menu.index', $request->only(['search', 'tipo']));
    }

    public function create(Request $request)
    {
        $categorias = Categoria::all();
        $selectedCategoriaId = $request->get('categoria_id');

        return view('admin.productos.create', compact('categorias', 'selectedCategoriaId'));
    }

    public function store(Request $request, ImageService $image)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categories,id',
            'tipo' => 'nullable|in:comida,bebida',
            'disponible' => 'boolean',
            'orden' => 'nullable|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mostrar_foto' => 'boolean',
        ]);

        $data = $request->except('foto');

        if (! isset($data['orden']) || $data['orden'] === '') {
            $grupo = isset($data['categoria_id']) && $data['categoria_id']
                ? Producto::where('categoria_id', $data['categoria_id'])
                : Producto::whereNull('categoria_id');
            $maxOrden = $grupo->max('orden');
            $data['orden'] = ($maxOrden ?? -1) + 1;
        }

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $image->optimizeAndStore(
                $request->file('foto'), 'productos', 800, 800, quality: 85
            );
        }

        Producto::create($data);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Producto $producto)
    {
        return view('admin.productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();

        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto, ImageService $image)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categories,id',
            'tipo' => 'nullable|in:comida,bebida',
            'disponible' => 'boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mostrar_foto' => 'boolean',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $image->deleteImage($producto->foto_path);
            $data['foto_path'] = $image->optimizeAndStore(
                $request->file('foto'), 'productos', 800, 800, quality: 85
            );
            $data['mostrar_foto'] = true;
        }

        if ($request->has('eliminar_foto') && $producto->foto_path) {
            $image->deleteImage($producto->foto_path);
            $data['foto_path'] = null;
        }

        $producto->update($data);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto, ImageService $image)
    {
        $image->deleteImage($producto->foto_path);

        $producto->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    public function cambiarCategoria(Request $request, Producto $producto)
    {
        $request->validate([
            'categoria_id' => 'nullable|exists:categories,id',
        ]);

        $categoriaId = $request->input('categoria_id');

        $data = ['categoria_id' => $categoriaId];

        $maxOrden = $categoriaId
            ? Producto::where('categoria_id', $categoriaId)->max('orden')
            : Producto::whereNull('categoria_id')->max('orden');
        $data['orden'] = ($maxOrden ?? -1) + 1;

        $producto->update($data);

        return response()->json(['success' => true]);
    }

    public function reordenar(Request $request)
    {
        $request->validate([
            'grupos' => 'required|array',
            'grupos.*.grupo' => 'required|string',
            'grupos.*.productos' => 'present|array',
            'grupos.*.productos.*' => 'integer|exists:productos,id',
            'grupos.*.categoria_id' => 'nullable|exists:categories,id',
        ]);

        $productos = Producto::whereIn('id', collect($request->input('grupos'))
            ->pluck('productos')
            ->flatten())
            ->get()
            ->keyBy('id');

        foreach ($request->input('grupos') as $grupo) {
            $categoriaId = $grupo['categoria_id'] ?? null;
            foreach (array_values($grupo['productos']) as $index => $id) {
                $producto = $productos->get($id);
                if (! $producto) {
                    continue;
                }
                $producto->categoria_id = $categoriaId;
                $producto->orden = $index;
                $producto->save();
            }
        }

        return response()->json(['success' => true]);
    }
}
