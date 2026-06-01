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
        $query = Producto::with('categoria');

        if ($search = $request->get('search')) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($categoria_id = $request->get('categoria_id')) {
            $query->where('categoria_id', $categoria_id);
        }

        $disponible = $request->input('disponible');
        if ($disponible !== null && $disponible !== '') {
            $query->where('disponible', $disponible);
        }

        $productos = $query->orderBy('nombre')->paginate(10);

        $categorias = Categoria::all();

        return view('admin.productos.index', compact('productos', 'categorias'));
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $image->optimizeAndStore(
                $request->file('foto'), 'productos', 800, 800, quality: 85
            );
        }

        Producto::create($data);

        return redirect()->route('admin.productos.index')
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
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $image->deleteImage($producto->foto_path);
            $data['foto_path'] = $image->optimizeAndStore(
                $request->file('foto'), 'productos', 800, 800, quality: 85
            );
        }

        if ($request->has('eliminar_foto') && $producto->foto_path) {
            $image->deleteImage($producto->foto_path);
            $data['foto_path'] = null;
        }

        $producto->update($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto, ImageService $image)
    {
        $image->deleteImage($producto->foto_path);

        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
