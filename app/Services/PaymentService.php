<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\ValueObjects\Money;

/**
 * SERVICE — encapsula uma fatia de lógica de negócio.
 *
 * Princípio aplicado: Single Responsibility (SRP)
 *   → PaymentService tem UMA responsabilidade: processar e estornar pagamentos.
 *   → Não calcula totais (OrderService faz isso), não valida requests (Controller faz isso).
 *
 * Princípio aplicado: Dependency Inversion (DIP)
 *   → O construtor recebe PaymentGatewayInterface — uma ABSTRAÇÃO.
 *   → PaymentService nunca instancia "new StripeGateway()" — isso seria acoplar ao concreto.
 *   → Quem decide qual gateway usar é o IoC Container (configurado no AppServiceProvider).
 *
 * Injeção de Dependência (DI):
 *   → A dependência (gateway) é INJETADA de fora, não criada internamente.
 *   → Vantagem em testes: você injeta um FakeGateway sem bater na API real.
 *       $service = new PaymentService(new FakeGateway());
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway, // depende da abstração, nunca do concreto
    ) {}

    public function pay(Money $amount, string $token): array
    {
        $transactionId = $this->gateway->charge($amount, $token);

        return [
            'transaction_id' => $transactionId,
            'gateway'        => $this->gateway->name(),
            'amount'         => $amount->formatted(),
            'status'         => 'approved',
        ];
    }

    public function refund(string $transactionId): array
    {
        $success = $this->gateway->refund($transactionId);

        return [
            'transaction_id' => $transactionId,
            'gateway'        => $this->gateway->name(),
            'status'         => $success ? 'refunded' : 'failed',
        ];
    }
}
