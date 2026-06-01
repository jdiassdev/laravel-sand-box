<?php

namespace App\Services;

use App\ValueObjects\Money;

/**
 * SERVICE — responsável apenas pela lógica de pedidos.
 *
 * Princípio aplicado: Single Responsibility (SRP)
 *   → OrderService sabe calcular totais e montar resumos de pedido.
 *   → NÃO sabe cobrar (PaymentService faz isso).
 *   → NÃO sabe validar requests HTTP (Controller faz isso).
 *
 * Por que separar em dois services (Order + Payment)?
 *   → Se a regra de cálculo de total mudar (ex: desconto progressivo),
 *     só OrderService é tocado — PaymentService não sente nada.
 *   → Se o gateway de pagamento mudar, só PaymentService é tocado.
 *   → Cada service tem seu próprio motivo para mudar — isso é SRP.
 */
class OrderService
{
    /**
     * Soma o valor total de todos os itens do pedido.
     * Usa Money::add() que retorna novas instâncias — imutabilidade preservada.
     *
     * @param  array<array{name: string, price_in_cents: int, quantity: int}>  $items
     */
    public function calculateTotal(array $items, string $currency = 'BRL'): Money
    {
        $total = Money::of(0, $currency);

        foreach ($items as $item) {
            $itemTotal = Money::of($item['price_in_cents'] * $item['quantity'], $currency);
            $total = $total->add($itemTotal); // $total anterior não muda — add() cria um novo Money
        }

        return $total;
    }

    /**
     * Monta o resumo legível do pedido para a resposta da API.
     *
     * @param  array<array{name: string, price_in_cents: int, quantity: int}>  $items
     */
    public function buildSummary(array $items, Money $total): array
    {
        return [
            'items'       => $items,
            'item_count'  => count($items),
            'total'       => $total->formatted(),
            'total_cents' => $total->amountInCents(),
            'currency'    => $total->currency(),
        ];
    }
}
