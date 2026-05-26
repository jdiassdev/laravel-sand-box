<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly Order $order) {}

    public function handle(): void
    {
        // Em produção: Mail::to($this->order->customer_email)->send(new OrderConfirmed($this->order));
        usleep(200_000); // simula latência do servidor de e-mail (200ms)

        $logFile = storage_path('logs/queue_demo.log');
        $line    = now()->format('H:i:s') . " [EMAIL] Para: {$this->order->customer_email} | Pedido #{$this->order->id} | {$this->order->product}\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    }
}
