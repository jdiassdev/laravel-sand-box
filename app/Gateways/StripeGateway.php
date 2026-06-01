<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\ValueObjects\Money;
use Illuminate\Support\Str;

/**
 * IMPLEMENTAÇÃO CONCRETA do contrato PaymentGatewayInterface.
 *
 * Princípio aplicado: Polimorfismo
 *   → StripeGateway e PaypalGateway implementam a mesma interface.
 *   → Qualquer código que receba PaymentGatewayInterface funciona com ambos
 *     sem saber qual é — troca transparente.
 *
 * Princípio aplicado: Single Responsibility (SRP)
 *   → Esta classe tem UMA responsabilidade: saber como falar com a API do Stripe.
 *   → Regras de negócio (calcular total, montar resumo) ficam nos Services.
 */
class StripeGateway implements PaymentGatewayInterface
{
    public function charge(Money $amount, string $token): string
    {
        // Em produção: chamada real à SDK do Stripe com $amount->amountInCents() e $token
        logger()->info('Stripe charge', [
            'amount' => $amount->formatted(),
            'token'  => $token,
        ]);

        return 'stripe_txn_' . Str::uuid();
    }

    public function refund(string $transactionId): bool
    {
        logger()->info('Stripe refund', ['transaction_id' => $transactionId]);

        return true;
    }

    public function name(): string
    {
        return 'stripe';
    }
}
