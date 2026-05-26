<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyWarehouseJob;
use App\Jobs\ProcessPaymentJob;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =========================================================================
    // PADRÃO 1: DISPATCH SIMPLES
    //
    // O job vai para a fila e o HTTP responde imediatamente.
    // O worker processa em background — o cliente não espera os 300ms do gateway.
    //
    // Sem fila: cliente espera 300ms (gateway) + 200ms (e-mail) + 100ms (estoque)
    // Com fila: cliente espera ~5ms (só gravar o pedido no banco)
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_email' => 'required|email',
            'product'        => 'required|string',
            'total'          => 'required|numeric|min:0.01',
        ]);

        $order = Order::create([
            'customer_email' => $request->customer_email,
            'product'        => $request->product,
            'total'          => $request->total,
            'status'         => 'pending',
        ]);

        // Despacha o job — retorna imediatamente, worker processa depois
        ProcessPaymentJob::dispatch($order);

        return response()->json([
            'message'  => 'Pedido recebido. Processando pagamento em background.',
            'order_id' => $order->id,
            'status'   => $order->status,
        ], 201);
    }

    // =========================================================================
    // PADRÃO 2: CHAIN — jobs em sequência garantida
    //
    // Se ProcessPaymentJob falhar, SendOrderConfirmationJob NÃO é executado.
    // A cadeia só avança se cada job anterior tiver sucesso.
    //
    // Cenário: só envia o e-mail de confirmação se o pagamento foi aprovado.
    // =========================================================================

    public function storeWithChain(Request $request): JsonResponse
    {
        $request->validate([
            'customer_email' => 'required|email',
            'product'        => 'required|string',
            'total'          => 'required|numeric|min:0.01',
        ]);

        $order = Order::create([
            'customer_email' => $request->customer_email,
            'product'        => $request->product,
            'total'          => $request->total,
            'status'         => 'pending',
        ]);

        // Chain: ProcessPayment → SendConfirmation → NotifyWarehouse
        // Ordem garantida, cada um espera o anterior terminar com sucesso.
        ProcessPaymentJob::dispatch($order)
            ->chain([
                new SendOrderConfirmationJob($order),
                new NotifyWarehouseJob($order),
            ]);

        return response()->json([
            'message'  => 'Pedido recebido. Pipeline de processamento iniciado.',
            'order_id' => $order->id,
            'pipeline' => ['ProcessPayment', 'SendConfirmation', 'NotifyWarehouse'],
        ], 201);
    }

    // =========================================================================
    // PADRÃO 3: DELAY — agenda o job para daqui a N segundos
    //
    // Casos de uso reais:
    //   - Enviar e-mail de "você não finalizou sua compra" 30 minutos depois
    //   - Cobrar cartão só no dia do vencimento
    //   - Lembrete de avaliação 7 dias após entrega
    // =========================================================================

    public function storeWithDelay(Request $request): JsonResponse
    {
        $request->validate([
            'customer_email' => 'required|email',
            'product'        => 'required|string',
            'total'          => 'required|numeric|min:0.01',
            'delay_seconds'  => 'integer|min:1|max:300',
        ]);

        $order = Order::create([
            'customer_email' => $request->customer_email,
            'product'        => $request->product,
            'total'          => $request->total,
            'status'         => 'pending',
        ]);

        $delay = $request->integer('delay_seconds', 30);

        ProcessPaymentJob::dispatch($order)->delay(now()->addSeconds($delay));

        return response()->json([
            'message'        => "Pedido recebido. Pagamento será processado em {$delay} segundos.",
            'order_id'       => $order->id,
            'scheduled_for'  => now()->addSeconds($delay)->toTimeString(),
        ], 201);
    }

    // =========================================================================
    // PADRÃO 4: FILA NOMEADA — separar jobs por prioridade
    //
    // Workers podem escutar filas específicas com prioridade diferente:
    //   php artisan queue:work --queue=payments,default
    //
    // Payments é processada primeiro. Default só é puxada quando payments esvazia.
    // Isso garante que pagamentos nunca fiquem atrás de notificações na fila.
    // =========================================================================

    public function storeInPriorityQueue(Request $request): JsonResponse
    {
        $request->validate([
            'customer_email' => 'required|email',
            'product'        => 'required|string',
            'total'          => 'required|numeric|min:0.01',
        ]);

        $order = Order::create([
            'customer_email' => $request->customer_email,
            'product'        => $request->product,
            'total'          => $request->total,
            'status'         => 'pending',
        ]);

        // Job de pagamento vai para fila de alta prioridade
        ProcessPaymentJob::dispatch($order)->onQueue('payments');

        // E-mail e estoque vão para fila padrão (menor prioridade)
        SendOrderConfirmationJob::dispatch($order)->onQueue('notifications');
        NotifyWarehouseJob::dispatch($order)->onQueue('default');

        return response()->json([
            'message'  => 'Jobs despachados em filas diferentes por prioridade.',
            'order_id' => $order->id,
            'queues'   => [
                'payments'      => 'ProcessPaymentJob (alta prioridade)',
                'notifications' => 'SendOrderConfirmationJob (média)',
                'default'       => 'NotifyWarehouseJob (baixa)',
            ],
        ], 201);
    }

    // =========================================================================
    // UTILITÁRIOS
    // =========================================================================

    public function show(Order $order): JsonResponse
    {
        return response()->json($order);
    }

    public function index(): JsonResponse
    {
        return response()->json(Order::latest()->take(20)->get());
    }

    public function queueStatus(): JsonResponse
    {
        $pending  = DB::table('jobs')->count();
        $failed   = DB::table('failed_jobs')->count();
        $logFile  = storage_path('logs/queue_demo.log');
        $logs     = file_exists($logFile)
            ? array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -20)
            : [];

        return response()->json([
            'jobs_pendentes' => $pending,
            'jobs_falhos'    => $failed,
            'ultimos_logs'   => $logs,
        ]);
    }
}
