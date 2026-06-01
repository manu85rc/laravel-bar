<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index()
    {
        $printers = Printer::orderBy('nombre')->get();

        return view('admin.printers.index', compact('printers'));
    }

    public function create()
    {
        return view('admin.printers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:cocina,bebidas,ambas,factura',
            'papel' => 'required|in:58mm,80mm',
            'ruta_dispositivo' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\/\-\.]+$/',
            'activo' => 'boolean',
        ]);

        Printer::create($request->only(['nombre', 'tipo', 'papel', 'ruta_dispositivo', 'activo']));

        return redirect()->route('admin.printers.index')
            ->with('success', 'Impresora creada exitosamente.');
    }

    public function edit(Printer $printer)
    {
        return view('admin.printers.edit', compact('printer'));
    }

    public function update(Request $request, Printer $printer)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:cocina,bebidas,ambas,factura',
            'papel' => 'required|in:58mm,80mm',
            'ruta_dispositivo' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\/\-\.]+$/',
            'activo' => 'boolean',
        ]);

        $printer->update($request->only(['nombre', 'tipo', 'papel', 'ruta_dispositivo', 'activo']));

        return redirect()->route('admin.printers.index')
            ->with('success', 'Impresora actualizada exitosamente.');
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();

        return redirect()->route('admin.printers.index')
            ->with('success', 'Impresora eliminada exitosamente.');
    }
}
