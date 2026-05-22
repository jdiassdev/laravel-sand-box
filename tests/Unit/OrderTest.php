<?php

// =============================================================================
// MODELO DE DOMÍNIO
//
// Em projetos reais, essa classe estaria em app/Domain/ ou app/Models/.
// Aqui ela fica junto ao teste para o arquivo ser autocontido.
// =============================================================================

class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'BRL',
    ) {}

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                "Cannot add {$this->currency} and {$other->currency}."
            );
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function formatted(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' ' . $this->currency;
    }
}

class OrderItem
{
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly Money $price,
        public readonly int $quantity,
    ) {}

    public function subtotal(): Money
    {
        return new Money($this->price->amount * $this->quantity, $this->price->currency);
    }
}

class Order
{
    // Status possíveis de um pedido — garante que só esses valores existam
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PAID      = 'paid';
    public const STATUS_SHIPPED   = 'shipped';
    public const STATUS_CANCELLED = 'cancelled';

    private string $status;

    /** @param OrderItem[] $items */
    public function __construct(
        public readonly string $id,
        private array $items = [],
        string $status = self::STATUS_PENDING,
    ) {
        $this->status = $status;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    public function total(): Money
    {
        $total = new Money(0.0);

        foreach ($this->items as $item) {
            $total = $total->add($item->subtotal());
        }

        return $total;
    }

    public function itemCount(): int
    {
        return count($this->items);
    }

    public function status(): string
    {
        return $this->status;
    }

    public function cancel(): void
    {
        // Regra de negócio: só pode cancelar se ainda não foi enviado
        if (in_array($this->status, [self::STATUS_SHIPPED, self::STATUS_CANCELLED])) {
            throw new \DomainException(
                "Cannot cancel an order with status '{$this->status}'."
            );
        }

        $this->status = self::STATUS_CANCELLED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'item_count' => $this->itemCount(),
            'total'      => $this->total()->amount,
        ];
    }
}

// =============================================================================
// HELPERS DE TESTE
//
// Funções auxiliares que criam objetos prontos para os testes.
// Isso é o padrão "Object Mother" — centraliza a criação de dados de teste
// e evita repetição em cada it()/test(). Se o construtor mudar, você
// atualiza só aqui, não em 30 testes.
// =============================================================================

function makeOrder(string $status = Order::STATUS_PENDING): Order
{
    return new Order(id: 'ORD-001', status: $status);
}

function makeItem(float $price = 100.0, int $quantity = 1): OrderItem
{
    return new OrderItem(
        sku: 'SKU-001',
        name: 'Produto Teste',
        price: new Money($price),
        quantity: $quantity,
    );
}

// =============================================================================
// PARTE 1 — it() vs test()
//
// As duas funções são idênticas. A diferença é só estilística:
//   - it()   → o nome vira uma frase: "it calculates the total"
//   - test() → mais direto, sem a convenção de frase
// =============================================================================

it('calculates the total based on items and quantities', function () {
    $order = makeOrder();
    $order->addItem(makeItem(price: 150.00, quantity: 2)); // 300,00
    $order->addItem(makeItem(price: 50.00,  quantity: 1)); //  50,00

    // toBe() usa igualdade estrita (===), equivalente ao assertSame()
    expect($order->total()->amount)->toBe(350.0);
});

test('new order has zero total when no items are added', function () {
    $order = makeOrder();

    expect($order->total()->amount)->toBe(0.0);
});

// =============================================================================
// PARTE 2 — EXPECTATIONS ENCADEADAS
//
// A grande vantagem do Pest sobre o PHPUnit puro é o encadeamento.
// Em vez de várias linhas de $this->assert*(), você escreve uma cadeia
// legível que descreve todas as propriedades esperadas de um valor.
//
// Regra mental: se você está testando mais de uma coisa no mesmo
// expect(), use encadeamento. Se são coisas distintas, use vários
// expect() separados para que as mensagens de erro sejam claras.
// =============================================================================

it('returns a Money object with correct values after summing items', function () {
    $order = makeOrder();
    $order->addItem(makeItem(price: 200.00, quantity: 3));

    $total = $order->total();

    // Testando o tipo e o valor em uma cadeia
    expect($total)
        ->toBeInstanceOf(Money::class);

    expect($total->amount)
        ->toBeFloat()
        ->toBeGreaterThan(0)
        ->toBe(600.0);

    expect($total->currency)
        ->toBeString()
        ->toBe('BRL')
        ->not->toBeEmpty(); // not-> inverte qualquer expectation
});

it('serializes to array with all expected keys', function () {
    $order = makeOrder();

    // toHaveKeys() verifica se o array tem EXATAMENTE essas chaves (pode ter outras)
    // toMatchArray() verifica chave+valor simultaneamente
    expect($order->toArray())
        ->toBeArray()
        ->toHaveKeys(['id', 'status', 'item_count', 'total'])
        ->toMatchArray([
            'id'         => 'ORD-001',
            'status'     => 'pending',
            'item_count' => 0,
            'total'      => 0.0,
        ]);
});

// =============================================================================
// PARTE 3 — TESTANDO EXCEPTIONS
//
// Para testar exceptions, você SEMPRE precisa envolver o código em uma
// closure (fn() => ...). Isso porque o Pest precisa executar o código
// internamente para interceptar a exception. Se você chamar direto,
// a exception vai estourar ANTES do expect() entrar em ação.
//
// Você pode verificar:
//   - Só a classe:             toThrow(MinhaException::class)
//   - Classe + mensagem:       toThrow(MinhaException::class, 'texto')
//   - Só a mensagem:           toThrow('texto da mensagem')
// =============================================================================

describe('Order cancellation rules', function () {

    it('can be cancelled when pending', function () {
        $order = makeOrder(Order::STATUS_PENDING);
        $order->cancel();

        // Verifica que o estado foi de fato alterado
        expect($order->status())->toBe(Order::STATUS_CANCELLED);
    });

    it('can be cancelled when paid', function () {
        $order = makeOrder(Order::STATUS_PAID);
        $order->cancel();

        expect($order->status())->toBe(Order::STATUS_CANCELLED);
    });

    it('throws DomainException when cancelling a shipped order', function () {
        $order = makeOrder(Order::STATUS_SHIPPED);

        // A closure é obrigatória — o Pest executa ela internamente
        // para capturar a exception sem deixar o teste estourar
        expect(fn () => $order->cancel())
            ->toThrow(\DomainException::class, "Cannot cancel an order with status 'shipped'.");
    });

    it('throws DomainException when cancelling an already cancelled order', function () {
        $order = makeOrder(Order::STATUS_CANCELLED);

        expect(fn () => $order->cancel())
            ->toThrow(\DomainException::class);
    });
});

// =============================================================================
// PARTE 4 — beforeEach() e afterEach()
//
// Hooks que executam antes/depois de CADA teste dentro do describe().
// Use beforeEach() para configurar o estado inicial (o "Arrange" do AAA).
// Use afterEach() para limpar recursos, fechar conexões, etc.
//
// beforeEach() é o equivalente ao setUp() do PHPUnit, mas funciona com
// closures — o que significa que você pode compartilhar variáveis entre
// o beforeEach e os testes usando a variável $this (dentro do describe).
// =============================================================================

describe('Money', function () {

    // $this->money será acessível em todos os it() dentro deste describe.
    // A anotação @var ensina o analisador estático sobre o tipo — sem ela,
    // o IDE reclama de "undefined property" porque não enxerga o binding
    // dinâmico que o Pest faz em tempo de execução.
    beforeEach(function () {
        /** @var Money $this->money */
        $this->money = new Money(100.00, 'BRL');
    });

    it('adds two money values of the same currency', function () {
        $result = $this->money->add(new Money(50.00, 'BRL'));

        expect($result->amount)->toBe(150.0);
        expect($result->currency)->toBe('BRL');
    });

    it('returns a new instance when adding (immutability)', function () {
        $result = $this->money->add(new Money(1.00));

        // Verifica que o objeto original não foi modificado (imutabilidade)
        expect($this->money->amount)->toBe(100.0);
        expect($result)->not->toBe($this->money); // não é o mesmo objeto
    });

    it('formats the value correctly for display', function () {
        expect($this->money->formatted())->toBe('100,00 BRL');
    });

    it('throws when adding money of different currencies', function () {
        expect(fn () => $this->money->add(new Money(50.00, 'USD')))
            ->toThrow(\InvalidArgumentException::class, 'Cannot add BRL and USD.');
    });
});

// =============================================================================
// PARTE 5 — skip() e todo()
//
// Ferramentas para gerenciar testes incompletos sem quebrar o CI.
//
//   ->skip()  → pula o teste mas registra na saída (aparece como "S")
//               Use quando o comportamento existe mas o teste está bloqueado
//               por algo externo (feature flag, ambiente, bug conhecido).
//
//   ->todo()  → marca como pendente de implementação
//               Use quando você mapeou o comportamento mas ainda não escreveu.
//               É uma forma de documentar o que DEVE ser testado.
// =============================================================================

it('compares two money values correctly')
    ->todo(); // ainda não implementado no modelo

it('applies discount coupon to the total', function () {
    // Este teste seria para uma feature de cupom que ainda não existe
    expect(true)->toBeTrue();
})->skip('Coupon feature not implemented yet — tracked in issue #42');
