<?php

namespace App;

use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    public function printTicket(string $devicePath, array $ticket): array
    {
        try {
            $connector = new FilePrintConnector($devicePath);
            $profile = CapabilityProfile::load('POS-5890');
            $printer = new Printer($connector, $profile);

            $type = $ticket['type'] ?? 'cocina';
            $papel = $ticket['papel'] ?? '80mm';
            $width = $papel === '58mm' ? 32 : 48;

            $printer->setJustification(Printer::JUSTIFY_CENTER);

            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->text($ticket['encabezado']."\n");
            $printer->selectPrintMode();

            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("PEDIDO #{$ticket['pedido_id']}\n");
            $printer->setEmphasis(false);

            $printer->text("Mesa: {$ticket['mesa']}\n");
            $printer->text("Staff: {$ticket['staff']}\n");
            $printer->text("Fecha: {$ticket['fecha']}\n");

            $printer->feed(1);

            if ($type === 'factura') {
                $this->printFacturaTicket($printer, $ticket, $width);
            } else {
                $this->printCocinaTicket($printer, $ticket, $width);
            }

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function printCocinaTicket(Printer $printer, array $ticket, int $width): void
    {
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text(str_repeat('-', $width)."\n");

        $printer->setEmphasis(true);
        $printer->text("CANT  PRODUCTO\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat('-', $width)."\n");

        foreach ($ticket['items'] as $item) {
            $line = $item['cantidad'].'x  '.$item['nombre'];
            if (mb_strlen($line) > $width) {
                $line = mb_substr($line, 0, $width - 1);
            }
            $printer->text($line."\n");
        }

        $printer->text(str_repeat('-', $width)."\n");
        $printer->feed(1);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("¡Gracias!\n");
    }

    private function printFacturaTicket(Printer $printer, array $ticket, int $width): void
    {
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("FACTURA\n");
        $printer->setEmphasis(false);

        $printer->feed(1);
        $printer->text(str_repeat('=', $width)."\n");

        $printer->setEmphasis(true);
        $printer->text("CANT  PRODUCTO\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat('-', $width)."\n");

        foreach ($ticket['items'] as $item) {
            $line = $item['cantidad'].'x  '.$item['nombre'];
            if (mb_strlen($line) > $width) {
                $line = mb_substr($line, 0, $width - 1);
            }
            $printer->text($line."\n");

            $precioStr = '$'.number_format($item['precio_unitario'], 0);
            $subtotalStr = '$'.number_format($item['subtotal'], 0);

            $printer->text("    a {$precioStr}\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Subtotal: {$subtotalStr}\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
        }

        $printer->text(str_repeat('=', $width)."\n");

        $totalStr = '$'.number_format($ticket['total'], 0);
        $printer->setEmphasis(true);
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text('TOTAL: '.$totalStr."\n");
        $printer->setEmphasis(false);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat('=', $width)."\n");

        $printer->feed(1);
        $printer->text("¡Gracias por su visita!\n");
    }
}
