<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyWarehouseJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly Order $order) {}

    public function handle(): void
    {
        // Em produção: Http::post('https://warehouse.api/orders', [...]);
        usleep(100_000); // simula chamada para sistema de estoque (100ms)

        $logFile = storage_path('logs/queue_demo.log');
        $line    = now()->format('H:i:s') . " [WAREHOUSE] Pedido #{$this->order->id} | {$this->order->product} | R$ {$this->order->total} | Status: {$this->order->status}\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    }
}
