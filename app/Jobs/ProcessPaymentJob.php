<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    // Quantas vezes o Laravel vai tentar re-executar se falhar
    public int $tries = 3;

    // Quantos segundos esperar entre tentativas (backoff exponencial)
    public array $backoff = [10, 30, 60];

    // Timeout máximo de execução em segundos
    public int $timeout = 60;

    public function __construct(public readonly Order $order) {}

    public function handle(): void
    {
        // Simula chamada ao gateway de pagamento (Stripe, PagSeguro, etc.)
        // Em produção: $response = Stripe::charge([...]);
        Log::info("ProcessPaymentJob: processando pagamento do pedido #{$this->order->id}");

        // Simula latência do gateway (300ms)
        usleep(300_000);

        // Simula falha apenas se $this->order->product contiver "FAIL"
        // Use product="FAIL-teste" para forçar falha e ver o retry/failed_jobs
        if (str_contains($this->order->product, 'FAIL')) {
            throw new \Exception("Gateway de pagamento indisponível — pedido #{$this->order->id}");
        }

        $this->order->update([
            'status'     => 'paid',
            'payment_id' => 'PAY-' . strtoupper(Str::random(8)),
        ]);

        Log::info("ProcessPaymentJob: pedido #{$this->order->id} pago com sucesso");
    }

    // Chamado quando todas as tentativas falharam
    public function failed(\Throwable $e): void
    {
        $this->order->update(['status' => 'failed']);

        Log::error("ProcessPaymentJob: pedido #{$this->order->id} falhou definitivamente: {$e->getMessage()}");
    }
}
