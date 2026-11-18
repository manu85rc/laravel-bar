<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $tipo = $request->get('tipo');

        $categorias = Categoria::with(['children.productos', 'productos'])
            ->whereNull('parent_id')
            ->get();

        $totalProductos = Producto::count();

        if ($search !== '' || $tipo !== null && $tipo !== '') {
            $filtrar = function ($productos) use ($search, $tipo) {
                return $productos->filter(function ($producto) use ($search, $tipo) {
                    if ($search !== '' && ! str_contains(mb_strtolower($producto->nombre), mb_strtolower($search))) {
                        return false;
                    }

                    if ($tipo !== null && $tipo !== '' && $producto->tipo !== $tipo) {
                        return false;
                    }

                    return true;
                })->values();
            };

            $categorias = $categorias->map(function ($categoria) use ($filtrar) {
                $categoria->setRelation('productos', $filtrar($categoria->productos));
                $categoria->children->each(function ($hija) use ($filtrar) {
                    $hija->setRelation('productos', $filtrar($hija->productos));
                });

                return $categoria;
            })->filter(function ($categoria) {
                $hijasConProductos = $categoria->children->contains(fn ($hija) => $hija->productos->isNotEmpty());

                return $categoria->productos->isNotEmpty() || $hijasConProductos;
            })->values();
        }

        $sinCategoria = Producto::with('categoria')
            ->whereNull('categoria_id')
            ->when($search !== '', fn ($q) => $q->where('nombre', 'like', '%'.$search.'%'))
            ->when($tipo !== null && $tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('admin.menu.index', compact('categorias', 'totalProductos', 'sinCategoria'));
    }
}
