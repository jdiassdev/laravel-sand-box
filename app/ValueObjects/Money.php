<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * VALUE OBJECT — representa um valor do domínio, não uma entidade.
 *
 * Características que definem um Value Object:
 *   1. IMUTÁVEL: depois de criado, nunca muda. Operações retornam novos objetos.
 *   2. SEM IDENTIDADE: dois Money(1000, 'BRL') são iguais — não importa qual é "o mesmo".
 *   3. ENCAPSULA REGRAS: sabe validar a si mesmo (valor negativo, moeda inválida).
 *
 * Por que "final"?
 *   → Impede herança. Value Objects não devem ser estendidos — a identidade do tipo
 *     faz parte do contrato. Um "ExtendedMoney" quebraria as garantias de imutabilidade.
 *
 * Por que "readonly"?
 *   → O PHP garante que as propriedades só podem ser escritas no construtor.
 *     Sem setter, sem mutação acidental depois da criação.
 *
 * Por que int (centavos) em vez de float?
 *   → Float tem erro de representação: 0.1 + 0.2 ≠ 0.3 em ponto flutuante.
 *     Trabalhar com centavos em int é exato e seguro para dinheiro.
 */
final class Money
{
    public function __construct(
        private readonly int $amountInCents,
        private readonly string $currency,
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter ISO code.');
        }
    }

    /**
     * Named constructor — forma expressiva de criar Money sem expor o construtor diretamente.
     * Money::of(1990, 'BRL') lê-se naturalmente: "dinheiro de 19,90 BRL".
     */
    public static function of(int $amountInCents, string $currency = 'BRL'): self
    {
        return new self($amountInCents, strtoupper($currency));
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function formatted(): string
    {
        return number_format($this->amountInCents / 100, 2, ',', '.') . ' ' . $this->currency;
    }

    /**
     * IMUTABILIDADE em ação: add() não altera $this nem $other.
     * Retorna um TERCEIRO objeto com o resultado — os originais ficam intactos.
     *
     * Exemplo:
     *   $a = Money::of(1000, 'BRL');
     *   $b = Money::of(500,  'BRL');
     *   $c = $a->add($b);  // $a=10,00  $b=5,00  $c=15,00 — nenhum mudou
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    /**
     * Igualdade por VALOR, não por referência.
     * Dois objetos Money com o mesmo valor e moeda são considerados iguais.
     */
    public function equals(Money $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} and {$other->currency}"
            );
        }
    }
}
