<?php

namespace App\Jobs;

use App\Models\Pedido;
use App\Services\PrintService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPrintOrderJob implements ShouldQueue
{
    use Dispatchable;

    public function __construct(
        public Pedido $pedido,
    ) {}

    public function handle(PrintService $printService): void
    {
        $printService->printOrder($this->pedido);
    }
}
