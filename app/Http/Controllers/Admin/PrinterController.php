<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\PrintQueueJob;
use App\Services\BluetoothService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrinterController extends Controller
{
    public function __construct(
        private BluetoothService $bluetooth,
    ) {}

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
            'conexion' => 'required|in:usb,bluetooth',
            'tipo' => 'required|in:cocina,bebidas,ambas,factura',
            'papel' => 'required|in:58mm,80mm',
            'ruta_dispositivo' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\/\-\. ]+$/',
            'baud_rate' => 'nullable|integer|in:9600,19200,38400,57600,115200',
            'activo' => 'boolean',
        ]);

        Printer::create($request->only(['nombre', 'conexion', 'tipo', 'papel', 'ruta_dispositivo', 'baud_rate', 'activo']));

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
            'conexion' => 'required|in:usb,bluetooth',
            'tipo' => 'required|in:cocina,bebidas,ambas,factura',
            'papel' => 'required|in:58mm,80mm',
            'ruta_dispositivo' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\/\-\. ]+$/',
            'baud_rate' => 'nullable|integer|in:9600,19200,38400,57600,115200',
            'activo' => 'boolean',
        ]);

        $printer->update($request->only(['nombre', 'conexion', 'tipo', 'papel', 'ruta_dispositivo', 'baud_rate', 'activo']));

        return redirect()->route('admin.printers.index')
            ->with('success', 'Impresora actualizada exitosamente.');
    }

    public function terminal()
    {
        $printers = Printer::where('activo', true)
            ->where('conexion', 'bluetooth')
            ->orderBy('nombre')
            ->get();

        $printersJson = $printers->map(fn ($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'baud_rate' => $p->baud_rate ?? 9600,
            'connected' => false,
            'connecting' => false,
            'testing' => false,
            'jobsPrinted' => 0,
            'errors' => 0,
            'lastPrint' => null,
            'errorMsg' => null,
            'log' => [],
        ])->values()->toJson();

        return view('admin.printers.terminal', compact('printers', 'printersJson'));
    }

    public function destroy(Printer $printer)
    {
        $printer->delete();

        return redirect()->route('admin.printers.index')
            ->with('success', 'Impresora eliminada exitosamente.');
    }

    public function testPrint(Request $request): JsonResponse
    {
        $request->validate([
            'printer_id' => 'required|integer|exists:printers,id',
        ]);

        $printer = Printer::findOrFail($request->input('printer_id'));

        $ticket = [
            'type' => 'cocina',
            'papel' => $printer->papel ?? '80mm',
            'encabezado' => 'TEST DE IMPRESION',
            'pedido_id' => 0,
            'mesa' => 'Mesa TEST',
            'staff' => 'Sistema',
            'fecha' => now()->format('d/m/Y H:i'),
            'items' => [
                ['nombre' => 'Item de prueba 1', 'cantidad' => 1],
                ['nombre' => 'Item de prueba 2', 'cantidad' => 2],
                ['nombre' => 'Papas fritas', 'cantidad' => 1],
            ],
        ];

        PrintQueueJob::create([
            'printer_id' => $printer->id,
            'ticket_data' => $ticket,
            'pending' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Ticket de prueba enviado a la cola']);
    }

    public function bluetoothScan(): JsonResponse
    {
        if (! $this->bluetooth->isAvailable()) {
            return response()->json([
                'success' => false,
                'error' => 'BlueZ no está instalado en el servidor. Instale bluez y bluez-tools.',
            ]);
        }

        $devices = $this->bluetooth->scanDevices();

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    public function usbDetect(): JsonResponse
    {
        try {
            $response = Http::timeout(5)
                ->get(config('services.print_server.url').'/devices');

            if (! $response->ok()) {
                Log::warning('Print server /devices responded with error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'El servidor de impresión respondió con error ('.$response->status().').',
                ]);
            }

            $data = $response->json();

            return response()->json([
                'success' => ($data['success'] ?? false),
                'devices' => $data['devices'] ?? [],
                'error' => $data['error'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to detect USB printers', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'No se pudo contactar el servidor de impresión.',
            ]);
        }
    }

    public function bluetoothPair(Request $request): JsonResponse
    {
        $request->validate([
            'mac' => 'required|string|regex:/^[A-F0-9:]{17}$/i',
            'pin' => 'nullable|string|max:10',
        ]);

        $mac = strtoupper($request->input('mac'));
        $pin = $request->input('pin') ?: null;
        $result = $this->bluetooth->pair($mac, $pin);

        return response()->json($result);
    }
}
