<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class OctaneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // =====================================================================
        // SINGLETON: a mesma instância para todos os requests
        //
        // O NotificationService captura o IP no construtor.
        // Como singleton, o IP fica congelado na primeira request.
        // Segunda request, terceira, décima — mesma instância, mesmo IP.
        // =====================================================================
        $this->app->singleton('notification.singleton', function () {
            return new NotificationService();
        });

        // =====================================================================
        // BIND: nova instância a cada vez que for resolvido
        //
        // O construtor roda de novo a cada request.
        // IP sempre correto para o request atual.
        // =====================================================================
        $this->app->bind('notification.bind', function () {
            return new NotificationService();
        });
    }

    public function boot(): void
    {
        // =====================================================================
        // TICK: tarefa periódica dentro do worker (só com Octane rodando)
        //
        // Cenário real: em vez de bater no banco a cada pageview,
        // acumula em cache e persiste a cada 60 segundos.
        // =====================================================================
        if (class_exists(\Laravel\Octane\Facades\Octane::class) && !$this->app->runningInConsole()) {
            \Laravel\Octane\Facades\Octane::tick('flush-pageviews', function () {
                $count = cache()->get('pageview_counter', 0);

                if ($count > 0) {
                    // Em produção: PageView::create(['count' => $count, 'date' => today()]);
                    cache()->put('last_flush_at', now()->toTimeString());
                    cache()->put('pageview_counter', 0);
                }
            })->seconds(60);
        }
    }
}
