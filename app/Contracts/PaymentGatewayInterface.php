<?php

namespace App\Contracts;

use App\ValueObjects\Money;

/**
 * INTERFACE — define um CONTRATO, não uma implementação.
 *
 * Uma interface responde à pergunta: "o que esse tipo de objeto sabe fazer?"
 * Ela não diz COMO faz — só GARANTE que quem implementar vai ter esses métodos.
 *
 * Princípio aplicado: Dependency Inversion (DIP)
 *   → Código de alto nível (PaymentService) depende desta abstração,
 *     não de Stripe, PayPal ou qualquer gateway concreto.
 *   → Trocar de gateway = trocar a implementação, nunca o contrato.
 *
 * Princípio aplicado: Open/Closed (OCP)
 *   → Adicionar PicPayGateway? Crie a classe, implemente esta interface.
 *   → Nenhum outro arquivo precisa ser alterado.
 */
interface PaymentGatewayInterface
{
    /**
     * Cobra o valor e retorna o ID da transação gerada pelo gateway.
     * O $token representa o token de pagamento do cliente (ex: token do cartão).
     */
    public function charge(Money $amount, string $token): string;

    /**
     * Estorna uma transação pelo seu ID. Retorna true se bem-sucedido.
     */
    public function refund(string $transactionId): bool;

    /**
     * Nome do gateway — útil para logs e rastreabilidade.
     */
    public function name(): string;
}
