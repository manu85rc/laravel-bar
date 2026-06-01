<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;

class MenuController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with(['children.productos', 'productos'])
            ->whereNull('parent_id')
            ->get();

        $totalProductos = Producto::count();

        return view('admin.menu.index', compact('categorias', 'totalProductos'));
    }
}
