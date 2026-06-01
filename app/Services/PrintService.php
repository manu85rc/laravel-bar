<?php

namespace App\Services;

use App\Models\Branding;
use App\Models\Pedido;
use App\Models\Printer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintService
{
    public function printOrder(Pedido $pedido): void
    {
        $printers = Printer::where('activo', true)
            ->whereIn('tipo', ['cocina', 'bebidas', 'ambas'])
            ->get();

        if ($printers->isEmpty()) {
            return;
        }

        $pedido->load(['mesa', 'staff', 'productos']);
        $branding = Branding::getInstance();

        $pedidoId = $pedido->id;
        $mesa = 'Mesa '.($pedido->mesa->numero ?? '?');
        $staff = $pedido->staff->name ?? '—';
        $fecha = $pedido->created_at->format('d/m/Y H:i');
        $encabezado = $branding->app_name ?? 'Mi Bar';
        if ($branding->phone) {
            $encabezado .= "\n".'Tel: '.$branding->phone;
        }

        foreach ($printers as $printer) {
            $items = $this->filterItems($pedido, $printer->tipo);

            if (empty($items)) {
                continue;
            }

            $payload = [
                'device' => $printer->ruta_dispositivo,
                'ticket' => [
                    'type' => 'cocina',
                    'papel' => $printer->papel ?? '80mm',
                    'encabezado' => $encabezado,
                    'pedido_id' => $pedidoId,
                    'mesa' => $mesa,
                    'staff' => $staff,
                    'fecha' => $fecha,
                    'items' => $items,
                ],
            ];

            $this->sendPrintJob($printer, $payload);
        }
    }

    public function printFactura(Pedido $pedido): bool
    {
        $printers = Printer::where('activo', true)
            ->where('tipo', 'factura')
            ->get();

        if ($printers->isEmpty()) {
            Log::warning('No factura printers configured');

            return false;
        }

        $pedido->load(['mesa', 'staff', 'productos']);
        $branding = Branding::getInstance();

        $pedidoId = $pedido->id;
        $mesa = 'Mesa '.($pedido->mesa->numero ?? '?');
        $staff = $pedido->staff->name ?? '—';
        $fecha = $pedido->created_at->format('d/m/Y H:i');
        $encabezado = $branding->app_name ?? 'Mi Bar';
        if ($branding->phone) {
            $encabezado .= "\n".'Tel: '.$branding->phone;
        }

        $items = [];
        foreach ($pedido->productos as $producto) {
            $cantidad = $producto->pivot->cantidad;
            $precioUnitario = (int) $producto->pivot->precio_unitario;
            $items[] = [
                'nombre' => $producto->nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $cantidad * $precioUnitario,
            ];
        }

        $anySuccess = false;

        foreach ($printers as $printer) {
            $payload = [
                'device' => $printer->ruta_dispositivo,
                'ticket' => [
                    'type' => 'factura',
                    'papel' => $printer->papel ?? '80mm',
                    'encabezado' => $encabezado,
                    'pedido_id' => $pedidoId,
                    'mesa' => $mesa,
                    'staff' => $staff,
                    'fecha' => $fecha,
                    'items' => $items,
                    'total' => (int) $pedido->total,
                ],
            ];

            $this->sendPrintJob($printer, $payload);
            $anySuccess = true;
        }

        return $anySuccess;
    }

    private function sendPrintJob(Printer $printer, array $payload): void
    {
        try {
            $response = Http::timeout(5)->post(
                config('services.print_server.url').'/print',
                $payload
            );

            if (! $response->successful()) {
                Log::warning('Print server responded with error', [
                    'printer' => $printer->nombre,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send print job', [
                'printer' => $printer->nombre,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function filterItems(Pedido $pedido, string $printerTipo): array
    {
        $items = [];

        foreach ($pedido->productos as $producto) {
            $productoTipo = $producto->tipo ?? 'comida';

            $shouldPrint = match ($printerTipo) {
                'cocina' => $productoTipo === 'comida',
                'bebidas' => $productoTipo === 'bebida',
                'ambas' => true,
                default => false,
            };

            if ($shouldPrint) {
                $items[] = [
                    'nombre' => $producto->nombre,
                    'cantidad' => $producto->pivot->cantidad,
                ];
            }
        }

        return $items;
    }
}
