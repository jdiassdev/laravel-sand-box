<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CONTROLLER FINO — apenas orquestra, nunca implementa regra de negócio.
 *
 * A regra de um controller bem escrito:
 *   1. Receber o request HTTP
 *   2. Validar a entrada
 *   3. Delegar para os services
 *   4. Retornar a resposta HTTP
 *
 * Se você ver lógica de negócio aqui (calcular desconto, verificar estoque,
 * montar totais), é sinal de que essa lógica deveria estar num Service.
 *
 * Injeção de Dependência via construtor:
 *   → Laravel resolve OrderService e PaymentService automaticamente.
 *   → PaymentService por sua vez recebe PaymentGatewayInterface resolvida pelo container.
 *   → O controller não sabe — e não precisa saber — que gateway está ativo.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * POST /api/oop/checkout
     *
     * Fluxo:
     *   1. Valida o payload
     *   2. OrderService calcula o total (lógica de pedido)
     *   3. PaymentService cobra via gateway ativo (lógica de pagamento)
     *   4. Retorna resumo + dados do pagamento
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.name'             => 'required|string',
            'items.*.price_in_cents'   => 'required|integer|min:1',
            'items.*.quantity'         => 'required|integer|min:1',
            'payment_token'            => 'required|string',
            'currency'                 => 'nullable|string|size:3',
        ]);

        $currency = strtoupper($request->input('currency', 'BRL'));

        // Delega cálculo para OrderService — controller não sabe como calcular
        $total   = $this->orderService->calculateTotal($request->items, $currency);
        $summary = $this->orderService->buildSummary($request->items, $total);

        // Delega pagamento para PaymentService — controller não sabe qual gateway é usado
        $payment = $this->paymentService->pay($total, $request->payment_token);

        return response()->json([
            'order'   => $summary,
            'payment' => $payment,
        ], 201);
    }

    /**
     * POST /api/oop/refund
     */
    public function refund(Request $request): JsonResponse
    {
        $request->validate(['transaction_id' => 'required|string']);

        $result = $this->paymentService->refund($request->transaction_id);

        return response()->json($result);
    }
}
