<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\ValueObjects\Money;
use Illuminate\Support\Str;

/**
 * SEGUNDA IMPLEMENTAÇÃO do mesmo contrato — demonstra o Polimorfismo.
 *
 * PaymentService não sabe se está falando com Stripe ou PayPal.
 * Ele só sabe que o objeto que recebeu obedece a PaymentGatewayInterface.
 * Isso é o poder de depender de abstrações.
 *
 * Para adicionar PicPay amanhã:
 *   1. Crie PicPayGateway implements PaymentGatewayInterface
 *   2. Mude o binding no AppServiceProvider
 *   3. Pronto — zero mudança em PaymentService, OrderController, ou qualquer outro lugar.
 */
class PaypalGateway implements PaymentGatewayInterface
{
    public function charge(Money $amount, string $token): string
    {
        // Em produção: chamada real à SDK do PayPal
        logger()->info('PayPal charge', [
            'amount' => $amount->formatted(),
            'token'  => $token,
        ]);

        return 'paypal_txn_' . Str::uuid();
    }

    public function refund(string $transactionId): bool
    {
        logger()->info('PayPal refund', ['transaction_id' => $transactionId]);

        return true;
    }

    public function name(): string
    {
        return 'paypal';
    }
}
