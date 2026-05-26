<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\NotificationService;
use App\Services\SlowReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Octane\Facades\Octane;

class OctaneController extends Controller
{
    // =========================================================================
    // CONCEITO 1: STATE LEAK
    //
    // Rotas para testar:
    //   POST /api/octane/cart/add?product=Tênis   → adiciona ao carrinho
    //   GET  /api/octane/cart                     → vê o estado atual
    //   DELETE /api/octane/cart                   → limpa
    //
    // Teste real:
    //   1. Chame POST com product=Tênis
    //   2. Chame POST com product=Camiseta
    //   3. Chame GET — o leaky mostra ['Tênis', 'Camiseta'] acumulado
    //      O safe mostra [] porque o CartService foi recriado nesse request
    // =========================================================================

    public function cartAdd(Request $request, CartService $cart): JsonResponse
    {
        $product = $request->query('product', 'Produto ' . rand(1, 99));

        $cart->addLeaky($product);
        $cart->addSafe($product);

        return response()->json([
            'adicionado' => $product,
            'leaky_cart' => $cart->getLeaky(),
            'safe_cart'  => $cart->getSafe(),
            'explicacao' => [
                'leaky' => 'static: acumula entre requests (bug)',
                'safe'  => 'instância: sempre começa vazio (correto)',
            ],
        ]);
    }

    public function cartView(CartService $cart): JsonResponse
    {
        return response()->json([
            'leaky_cart' => $cart->getLeaky(),
            'safe_cart'  => $cart->getSafe(),
        ]);
    }

    public function cartClear(CartService $cart): JsonResponse
    {
        $cart->clearLeaky();

        return response()->json(['status' => 'carrinho leaky limpo']);
    }

    // =========================================================================
    // CONCEITO 2: SINGLETON vs BIND
    //
    // Rotas para testar:
    //   GET /api/octane/notify/singleton  → usa o singleton (IP congelado)
    //   GET /api/octane/notify/bind       → usa o bind (IP correto)
    //
    // Teste real:
    //   1. Chame /singleton — veja captured_at e current_time
    //   2. Espere alguns segundos, chame /singleton de novo
    //      O captured_at não muda — o objeto nunca foi recriado
    //   3. Chame /bind — captured_at sempre igual a current_time
    //      Novo objeto a cada request
    // =========================================================================

    public function notifySingleton(): JsonResponse
    {
        // Resolve 3 vezes o MESMO binding dentro de um request.
        // Singleton: as 3 resoluções retornam o MESMO objeto (mesmo instance_id).
        // Isso é o que acontece no Octane entre requests também —
        // o objeto vive enquanto o worker estiver de pé.
        $a = app()->make('notification.singleton');
        $b = app()->make('notification.singleton');
        $c = app()->make('notification.singleton');

        return response()->json([
            'modo'      => 'SINGLETON — mesmo objeto nas 3 resoluções',
            'resolve_a' => $a->send('primeira resolução'),
            'resolve_b' => $b->send('segunda resolução'),
            'resolve_c' => $c->send('terceira resolução'),
            'conclusao' => $a->instanceId === $b->instanceId && $b->instanceId === $c->instanceId
                ? 'instance_id IGUAL nas 3 — singleton funcionando'
                : 'instance_ids diferentes — não é singleton',
        ]);
    }

    public function notifyBind(): JsonResponse
    {
        // Resolve 3 vezes o binding de bind dentro de um request.
        // Bind: cada resolução cria um objeto NOVO (instance_id diferente).
        $a = app()->make('notification.bind');
        $b = app()->make('notification.bind');
        $c = app()->make('notification.bind');

        return response()->json([
            'modo'      => 'BIND — objeto novo a cada resolução',
            'resolve_a' => $a->send('primeira resolução'),
            'resolve_b' => $b->send('segunda resolução'),
            'resolve_c' => $c->send('terceira resolução'),
            'conclusao' => $a->instanceId !== $b->instanceId
                ? 'instance_ids DIFERENTES — bind criando novo objeto por resolução'
                : 'instance_ids iguais — inesperado',
        ]);
    }

    public function notifyReset(): JsonResponse
    {
        NotificationService::resetCounter();

        return response()->json(['status' => 'contador zerado']);
    }

    // =========================================================================
    // CONCEITO 3: SEQUENTIAL vs CONCURRENT
    //
    // Rotas para testar:
    //   GET /api/octane/dashboard/sequential  → soma todos os tempos
    //   GET /api/octane/dashboard/concurrent  → tempo do mais lento
    //
    // Teste real:
    //   1. Chame /sequential — veja total_ms (~650ms)
    //   2. Chame /concurrent — veja total_ms (~300ms, o mais lento dos 3)
    //
    // ATENÇÃO: concurrent só funciona com Octane rodando (Linux/WSL).
    // Em desenvolvimento, o fallback cai para sequencial mas mostra o conceito.
    // =========================================================================

    public function dashboardSequential(): JsonResponse
    {
        $service = new SlowReportService();
        $start   = microtime(true);

        // Cada operação espera a anterior terminar
        $revenue = $service->totalRevenue();
        $orders  = $service->pendingOrders();
        $users   = $service->newUsersToday();

        $total = round((microtime(true) - $start) * 1000);

        return response()->json([
            'modo'       => 'SEQUENCIAL — soma todos os tempos',
            'total_ms'   => "{$total}ms",
            'esperado'   => '~650ms (300+200+150)',
            'revenue'    => $revenue,
            'orders'     => $orders,
            'users'      => $users,
        ]);
    }

    public function dashboardConcurrent(): JsonResponse
    {
        $service = new SlowReportService();
        $start   = microtime(true);

        // Com Octane (Swoole/FrankenPHP): roda em paralelo via corrotinas
        // Sem Octane: Octane::concurrently() não está disponível — demonstra o conceito
        if (app()->bound(\Laravel\Octane\Octane::class)) {
            [$revenue, $orders, $users] = Octane::concurrently([
                fn () => $service->totalRevenue(),
                fn () => $service->pendingOrders(),
                fn () => $service->newUsersToday(),
            ]);
            $modo = 'OCTANE CONCURRENT — paralelo real via corrotinas';
        } else {
            // Fallback para demonstração sem Octane
            $revenue = $service->totalRevenue();
            $orders  = $service->pendingOrders();
            $users   = $service->newUsersToday();
            $modo    = 'FALLBACK SEQUENCIAL (Octane não está rodando)';
        }

        $total = round((microtime(true) - $start) * 1000);

        return response()->json([
            'modo'     => $modo,
            'total_ms' => "{$total}ms",
            'esperado_com_octane' => '~300ms (o mais lento dos 3)',
            'revenue'  => $revenue,
            'orders'   => $orders,
            'users'    => $users,
        ]);
    }

    // =========================================================================
    // CONCEITO 4: TICKS
    //
    // Rota para testar:
    //   GET /api/octane/ticks/status → mostra o estado do cache que o tick gerencia
    //
    // O tick está registrado no OctaneServiceProvider.
    // Sem Octane rodando, o tick não dispara — mas você pode ver o cache
    // sendo manipulado manualmente para entender o conceito.
    // =========================================================================

    public function ticksStatus(): JsonResponse
    {
        $counterKey = 'pageview_counter';
        $lastFlush  = cache()->get('last_flush_at', 'nunca');
        $counter    = cache()->get($counterKey, 0);

        // Simula incremento (o que aconteceria em cada pageview)
        cache()->increment($counterKey);

        return response()->json([
            'explicacao'    => 'O tick flush-pageviews roda a cada 60s e grava o contador no banco',
            'counter_agora' => $counter + 1,
            'last_flush'    => $lastFlush,
            'proximo_flush' => 'automático pelo tick (só com Octane rodando)',
        ]);
    }
}
